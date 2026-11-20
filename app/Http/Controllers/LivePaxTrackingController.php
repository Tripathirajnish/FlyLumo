<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class LivePaxTrackingController extends Controller
{
    public function heartbeat(Request $request)
    {
        if (!config('live_pax.enabled', true) || !Schema::hasTable('live_pax_sessions')) {
            return response()->json(['ok' => true, 'disabled' => true]);
        }

        $data = $request->validate([
            'session_id' => 'required|string|max:80',
            'visitor_id' => 'required|string|max:80',
            'path' => 'nullable|string|max:500',
            'title' => 'nullable|string|max:255',
            'referrer' => 'nullable|string|max:1000',
            'device' => 'nullable|string|max:24',
            'browser' => 'nullable|string|max:80',
            'os' => 'nullable|string|max:80',
            'viewport_width' => 'nullable|integer|min:0|max:10000',
            'viewport_height' => 'nullable|integer|min:0|max:10000',
            'last_interaction_ms' => 'nullable|integer|min:0',
        ]);

        $sessionKey = $this->safeKey($data['session_id']);
        $visitorKey = $this->safeKey($data['visitor_id']);
        $path = $this->safePath($data['path'] ?? '/');
        $now = now();
        $activityAt = $now;
        if (!empty($data['last_interaction_ms'])) {
            $candidate = \Carbon\Carbon::createFromTimestampMs((int)$data['last_interaction_ms']);
            if ($candidate->between($now->copy()->subHours(6), $now->copy()->addMinute())) $activityAt = $candidate;
        }

        $existing = DB::table('live_pax_sessions')->where('session_key', $sessionKey)->first();
        $location = $this->location($request);
        $stage = $this->stage($path);
        $sensitive = $this->sensitive($path);

        if (!$existing) {
            $id = DB::table('live_pax_sessions')->insertGetId([
                'session_key' => $sessionKey,
                'visitor_key' => $visitorKey,
                'current_path' => $path,
                'current_title' => Str::limit(strip_tags((string)($data['title'] ?? '')), 255, ''),
                'funnel_stage' => $stage,
                'device_type' => $data['device'] ?? null,
                'browser' => $data['browser'] ?? null,
                'operating_system' => $data['os'] ?? null,
                'country' => $location['country'],
                'country_code' => $location['country_code'],
                'region' => $location['region'],
                'city' => $location['city'],
                'referrer' => Str::limit((string)($data['referrer'] ?? ''), 1000, ''),
                'source' => $this->source((string)($data['referrer'] ?? '')),
                'ip_hash' => hash('sha256', (string)$request->ip().config('app.key')),
                'viewport_width' => $data['viewport_width'] ?? null,
                'viewport_height' => $data['viewport_height'] ?? null,
                'pages_visited' => 1,
                'started_at' => $now,
                'last_activity_at' => $activityAt,
                'last_heartbeat_at' => $now,
                'recording_enabled' => (bool)config('live_pax.recording_enabled', true),
                'is_sensitive_page' => $sensitive,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $this->openPageView($id, $path, $data['title'] ?? null, $stage, $sensitive, $now);
        } else {
            $changedPage = (string)$existing->current_path !== $path;
            $update = [
                'current_path' => $path,
                'current_title' => Str::limit(strip_tags((string)($data['title'] ?? '')), 255, ''),
                'funnel_stage' => $stage,
                'device_type' => $data['device'] ?? $existing->device_type,
                'browser' => $data['browser'] ?? $existing->browser,
                'operating_system' => $data['os'] ?? $existing->operating_system,
                'viewport_width' => $data['viewport_width'] ?? $existing->viewport_width,
                'viewport_height' => $data['viewport_height'] ?? $existing->viewport_height,
                'last_activity_at' => $activityAt,
                'last_heartbeat_at' => $now,
                'ended_at' => null,
                'is_sensitive_page' => $sensitive,
                'updated_at' => $now,
            ];
            if ($changedPage) $update['pages_visited'] = ((int)$existing->pages_visited) + 1;
            DB::table('live_pax_sessions')->where('id', $existing->id)->update($update);
            if ($changedPage) {
                $this->closeOpenPageView((int)$existing->id, $now);
                $this->openPageView((int)$existing->id, $path, $data['title'] ?? null, $stage, $sensitive, $now);
            }
        }

        return response()->json([
            'ok' => true,
            'heartbeat_seconds' => (int)config('live_pax.heartbeat_seconds', 15),
            'sensitive' => $sensitive,
            'recording' => (bool)config('live_pax.recording_enabled', true),
        ]);
    }

    public function chunk(Request $request)
    {
        if (!config('live_pax.enabled', true) || !config('live_pax.recording_enabled', true)) return response()->json(['ok' => true]);
        if (!Schema::hasTable('live_pax_sessions') || !Schema::hasTable('live_pax_recording_chunks')) return response()->json(['ok' => true]);

        $data = $request->validate([
            'session_id' => 'required|string|max:80',
            'sequence' => 'required|integer|min:0|max:4294967295',
            'path' => 'nullable|string|max:500',
            'started_ms' => 'nullable|integer|min:0',
            'ended_ms' => 'nullable|integer|min:0',
            'events' => 'required|array|max:600',

            // Live Pax v4 snapshots are transported in small ordered parts.
            // Keep the validation explicit so malformed/oversized parts cannot
            // become an unbounded recording payload.
            'events.*.type' => 'nullable|string|max:40',
            'events.*.snapshot_id' => 'nullable|string|max:100',
            'events.*.part_index' => 'nullable|integer|min:0|max:100',
            'events.*.part_total' => 'nullable|integer|min:1|max:100',
            'events.*.html_part' => 'nullable|string|max:150000',
        ]);
        $session = DB::table('live_pax_sessions')->where('session_key', $this->safeKey($data['session_id']))->first();
        if (!$session) return response()->json(['ok' => false], 404);

        // Snapshot-part transport validation. Each part is deliberately small,
        // and all parts for one snapshot must describe a sane ordered set.
        foreach ($data['events'] as $event) {
            if (($event['type'] ?? '') !== 'snapshot_part') continue;

            $part = (string)($event['html_part'] ?? '');
            $index = (int)($event['part_index'] ?? -1);
            $total = (int)($event['part_total'] ?? 0);
            $snapshotId = (string)($event['snapshot_id'] ?? '');

            if ($snapshotId === '' || $total < 1 || $total > 100 || $index < 0 || $index >= $total) {
                return response()->json(['ok' => false, 'message' => 'invalid_snapshot_part'], 422);
            }

            if (strlen($part) > 150000) {
                return response()->json(['ok' => false, 'message' => 'snapshot_part_too_large'], 413);
            }
        }

        $payload = json_encode($data['events'], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
        if ($payload === false || strlen($payload) > (int)config('live_pax.max_chunk_bytes', 800000)) {
            return response()->json(['ok' => false, 'message' => 'chunk_too_large'], 413);
        }

        DB::table('live_pax_recording_chunks')->updateOrInsert(
            ['session_id' => $session->id, 'sequence' => (int)$data['sequence']],
            [
                'path' => $this->safePath($data['path'] ?? $session->current_path ?? '/'),
                'started_ms' => $data['started_ms'] ?? null,
                'ended_ms' => $data['ended_ms'] ?? null,
                'event_count' => count($data['events']),
                'payload_json' => $payload,
                'created_at' => now(),
            ]
        );

        if (Schema::hasTable('live_pax_interactions')) {
            $rows = [];
            foreach ($data['events'] as $event) {
                $type = (string)($event['type'] ?? '');
                if (!in_array($type, ['click','rage_click','dead_click','form_interaction','js_error'], true)) continue;
                $rows[] = [
                    'session_id' => $session->id,
                    'event_type' => $type,
                    'path' => $this->safePath($data['path'] ?? $session->current_path ?? '/'),
                    'x' => isset($event['x']) ? max(0, min(10000, (int)$event['x'])) : null,
                    'y' => isset($event['y']) ? max(0, min(100000, (int)$event['y'])) : null,
                    'viewport_width' => isset($event['vw']) ? max(0, min(10000, (int)$event['vw'])) : null,
                    'viewport_height' => isset($event['vh']) ? max(0, min(10000, (int)$event['vh'])) : null,
                    'target_tag' => Str::limit((string)($event['tag'] ?? ''), 40, ''),
                    'target_hint' => Str::limit((string)($event['hint'] ?? ''), 160, ''),
                    'relative_ms' => isset($event['t']) ? max(0, (int)$event['t']) : null,
                    'created_at' => now(),
                ];
                if (count($rows) >= 100) break;
            }
            if ($rows) DB::table('live_pax_interactions')->insert($rows);
        }

        return response()->json(['ok' => true]);
    }

    private function safeKey(string $key): string
    {
        return preg_replace('/[^A-Za-z0-9_.:-]/', '', substr($key, 0, 80)) ?: Str::random(32);
    }
    private function safePath(?string $path): string
    {
        $path = parse_url((string)$path, PHP_URL_PATH) ?: '/';
        return Str::limit('/'.ltrim($path, '/'), 500, '');
    }
    private function sensitive(string $path): bool
    {
        foreach ((array)config('live_pax.sensitive_path_patterns', []) as $pattern) if (@preg_match($pattern, $path)) return true;
        return false;
    }
    private function stage(string $path): string
    {
        $p = strtolower($path);
        if (str_contains($p, 'booking-successful') || str_contains($p, 'confirmation')) return 'Confirmation';
        if (str_contains($p, 'flight-payment') || str_contains($p, 'flights-payment') || str_contains($p, 'payment')) return 'Payment';
        if (str_contains($p, 'passenger')) return 'Passenger Details';
        if (str_contains($p, 'flight-listing')) return 'Flight Results';
        if (str_contains($p, 'flight-detail')) return 'Flight Details';
        if ($p === '/' || $p === '') return 'Home';
        if (str_contains($p, 'search')) return 'Searching Flights';
        return 'Browsing';
    }
    private function source(string $referrer): string
    {
        if ($referrer === '') return 'Direct';
        $host = strtolower((string)parse_url($referrer, PHP_URL_HOST));
        if ($host === '') return 'Referral';
        if (str_contains($host, 'google.')) return 'Google';
        if (str_contains($host, 'bing.')) return 'Bing';
        if (str_contains($host, 'facebook.') || str_contains($host, 'instagram.')) return 'Meta';
        if (str_contains($host, 'fareleader')) return 'FareLeaders';
        return Str::limit($host, 120, '');
    }
    private function location(Request $request): array
    {
        $code = strtoupper((string)($request->header('CF-IPCountry') ?: $request->header('X-Vercel-IP-Country') ?: $request->header('CloudFront-Viewer-Country') ?: ''));
        $country = $code ?: 'Unknown';
        $map = ['US'=>'United States','IN'=>'India','GB'=>'United Kingdom','CA'=>'Canada','AU'=>'Australia','AE'=>'United Arab Emirates','FR'=>'France','DE'=>'Germany','JP'=>'Japan','MX'=>'Mexico'];
        return [
            'country_code' => $code ?: null,
            'country' => $map[$code] ?? ($code ?: 'Unknown'),
            'region' => $request->header('CF-Region') ?: $request->header('X-Vercel-IP-Country-Region') ?: null,
            'city' => $request->header('CF-IPCity') ?: $request->header('X-Vercel-IP-City') ?: null,
        ];
    }
    private function openPageView(int $sessionId, string $path, ?string $title, string $stage, bool $sensitive, $now): void
    {
        if (!Schema::hasTable('live_pax_page_views')) return;
        DB::table('live_pax_page_views')->insert([
            'session_id'=>$sessionId,'path'=>$path,'page_title'=>Str::limit(strip_tags((string)$title),255,''),
            'funnel_stage'=>$stage,'entered_at'=>$now,'sensitive'=>$sensitive,'created_at'=>$now,'updated_at'=>$now
        ]);
    }
    private function closeOpenPageView(int $sessionId, $now): void
    {
        if (!Schema::hasTable('live_pax_page_views')) return;
        $pv = DB::table('live_pax_page_views')->where('session_id',$sessionId)->whereNull('exited_at')->orderByDesc('id')->first();
        if ($pv) DB::table('live_pax_page_views')->where('id',$pv->id)->update([
            'exited_at'=>$now,'duration_seconds'=>max(0,$now->diffInSeconds(\Carbon\Carbon::parse($pv->entered_at))),'updated_at'=>$now
        ]);
    }
}
