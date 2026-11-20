<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class LivePaxMonitorController extends Controller
{
    private function guard(Request $request)
    {
        return $request->session()->has('crm_admin_id') ? null : redirect()->route('crm.login');
    }

    public function index(Request $request)
    {
        if ($x = $this->guard($request)) return $x;
        return view('crm.live-pax.index');
    }

    public function replay(Request $request, int $id)
    {
        if ($x = $this->guard($request)) return $x;
        abort_unless(Schema::hasTable('live_pax_sessions'), 404);
        $session = DB::table('live_pax_sessions')->where('id', $id)->first();
        abort_unless($session, 404);
        return view('crm.live-pax.replay', compact('session'));
    }

    public function data(Request $request)
    {
        if ($x = $this->guard($request)) return $x;
        if (!Schema::hasTable('live_pax_sessions')) return response()->json($this->emptyData());

        $activeSeconds = (int)config('live_pax.active_seconds', 25);
        $offlineSeconds = (int)config('live_pax.offline_seconds', 60);
        $now = now();
        $liveCutoff = $now->copy()->subSeconds($offlineSeconds);
        $activeCutoff = $now->copy()->subSeconds($activeSeconds);

        $liveQ = DB::table('live_pax_sessions')->where('last_heartbeat_at', '>=', $liveCutoff);
        $live = (clone $liveQ)->orderByDesc('last_activity_at')->limit(250)->get()->map(function ($s) use ($activeCutoff, $now) {
            $s->status = \Carbon\Carbon::parse($s->last_activity_at)->gte($activeCutoff) ? 'Active' : 'Idle';
            $s->duration_seconds = max(0, $now->diffInSeconds(\Carbon\Carbon::parse($s->started_at)));
            $s->last_seen_seconds = max(0, $now->diffInSeconds(\Carbon\Carbon::parse($s->last_heartbeat_at)));
            return $s;
        });

        [$from, $to] = $this->range($request);
        $recordedQ = DB::table('live_pax_sessions')->whereBetween('started_at', [$from, $to]);
        $this->applyFilters($recordedQ, $request);
        $recorded = $recordedQ->orderByDesc('started_at')->limit(300)->get()->map(function ($s) use ($now, $offlineSeconds) {
            $end = $s->ended_at ? \Carbon\Carbon::parse($s->ended_at) : \Carbon\Carbon::parse($s->last_heartbeat_at ?? $s->started_at);
            $s->duration_seconds = max(0, $end->diffInSeconds(\Carbon\Carbon::parse($s->started_at)));
            $s->status = \Carbon\Carbon::parse($s->last_heartbeat_at ?? $s->started_at)->gte($now->copy()->subSeconds($offlineSeconds)) ? 'Live' : 'Offline';
            return $s;
        });

        $stats = [
            'online' => $live->count(),
            'active' => $live->where('status', 'Active')->count(),
            'idle' => $live->where('status', 'Idle')->count(),
            'payment' => $live->where('funnel_stage', 'Payment')->count(),
            'results' => $live->where('funnel_stage', 'Flight Results')->count(),
        ];

        $analytics = $this->analyticsPayload($from, $to);
        return response()->json(compact('stats', 'live', 'recorded', 'analytics'));
    }

    public function session(Request $request, int $id)
    {
        if ($x = $this->guard($request)) return $x;
        abort_unless(Schema::hasTable('live_pax_sessions'), 404);
        $session = DB::table('live_pax_sessions')->where('id', $id)->first();
        abort_unless($session, 404);

        $pages = Schema::hasTable('live_pax_page_views')
            ? DB::table('live_pax_page_views')->where('session_id', $id)->orderBy('id')->get()
            : collect();

        $friction = Schema::hasTable('live_pax_interactions')
            ? DB::table('live_pax_interactions')
                ->where('session_id', $id)
                ->whereIn('event_type', ['rage_click','dead_click','js_error','form_interaction'])
                ->select('event_type', DB::raw('COUNT(*) total'))
                ->groupBy('event_type')
                ->pluck('total', 'event_type')
            : collect();

        return response()->json(compact('session','pages','friction'));
    }

    public function chunks(Request $request, int $id)
    {
        if ($x = $this->guard($request)) return $x;

        $after = max(0, (int)$request->query('after', 0));
        $debug = $request->boolean('debug');

        $rows = Schema::hasTable('live_pax_recording_chunks')
            ? DB::table('live_pax_recording_chunks')
                ->where('session_id', $id)
                ->where('id', '>', $after)
                ->orderBy('id')
                ->limit(120)
                ->get()
            : collect();

        $debugRows = [];

        $rows->transform(function ($r) use ($debug, &$debugRows) {
            $events = json_decode($r->payload_json, true) ?: [];

            if ($debug) {
                $snapshots = collect($events)->filter(fn($e) => ($e['type'] ?? null) === 'snapshot');

                $debugRows[] = [
                    'chunk_id' => (int)$r->id,
                    'sequence' => (int)($r->sequence ?? 0),
                    'event_count' => count($events),
                    'snapshot_count' => $snapshots->count(),
                    'snapshot_lengths' => $snapshots->map(fn($e) => strlen((string)($e['html'] ?? '')))->values()->all(),
                    'snapshot_text_lengths' => $snapshots->map(function ($e) {
                        $html = (string)($e['html'] ?? '');
                        return strlen(trim(preg_replace('/\s+/', ' ', strip_tags($html))));
                    })->values()->all(),
                    'has_body' => $snapshots->contains(fn($e) => stripos((string)($e['html'] ?? ''), '<body') !== false),
                    'has_flight_listing_marker' => $snapshots->contains(function ($e) {
                        $html = strtolower((string)($e['html'] ?? ''));
                        return str_contains($html, 'flight-list')
                            || str_contains($html, 'flight result')
                            || str_contains($html, 'flight-result')
                            || str_contains($html, 'select flight');
                    }),
                ];
            }

            $r->events = $events;
            unset($r->payload_json);

            return $r;
        });

        $response = [
            'chunks' => $rows,
            'last_id' => (int)($rows->last()->id ?? $after),
        ];

        if ($debug) {
            $response['debug'] = [
                'session_id' => $id,
                'chunks_returned' => $rows->count(),
                'summary' => $debugRows,
            ];
        }

        return response()->json($response);
    }

    public function heatmap(Request $request)
    {
        if ($x = $this->guard($request)) return $x;
        if (!Schema::hasTable('live_pax_interactions')) return response()->json(['points'=>[]]);

        [$from,$to] = $this->range($request);
        $path = $this->safePath((string)$request->query('path','/'));

        $rows = DB::table('live_pax_interactions')
            ->where('event_type','click')
            ->where('path',$path)
            ->whereBetween('created_at',[$from,$to])
            ->whereNotNull('x')
            ->whereNotNull('y')
            ->where('viewport_width','>',0)
            ->limit(5000)
            ->get();

        $points = $rows->map(fn($r)=>[
            'x'=>round(((int)$r->x/(int)$r->viewport_width)*100,2),
            'y'=>(int)$r->y,
        ])->values();

        return response()->json(['path'=>$path,'points'=>$points,'count'=>$points->count()]);
    }

    private function range(Request $request): array
    {
        $to = $request->filled('to')
            ? \Carbon\Carbon::parse($request->query('to'))->endOfDay()
            : now()->endOfDay();

        $from = $request->filled('from')
            ? \Carbon\Carbon::parse($request->query('from'))->startOfDay()
            : now()->subDays(6)->startOfDay();

        if ($from->gt($to)) {
            [$from,$to] = [$to->copy()->startOfDay(),$from->copy()->endOfDay()];
        }

        if ($from->diffInDays($to) > 365) {
            $from = $to->copy()->subDays(365);
        }

        return [$from,$to];
    }

    private function applyFilters($q, Request $request): void
    {
        if ($request->filled('device')) $q->where('device_type', $request->query('device'));
        if ($request->filled('country')) $q->where('country', $request->query('country'));
        if ($request->filled('stage')) $q->where('funnel_stage', $request->query('stage'));
        if ($request->filled('path')) $q->where('current_path', 'like', '%'.$this->safePath($request->query('path')).'%');
    }

    private function analyticsPayload($from, $to): array
    {
        $pages = Schema::hasTable('live_pax_page_views')
            ? DB::table('live_pax_page_views')->whereBetween('entered_at',[$from,$to])
            : null;

        $interactions = Schema::hasTable('live_pax_interactions')
            ? DB::table('live_pax_interactions')->whereBetween('created_at',[$from,$to])
            : null;

        return [
            'top_pages' => $pages
                ? (clone $pages)->select('path',DB::raw('COUNT(*) views'))->groupBy('path')->orderByDesc('views')->limit(20)->get()
                : [],
            'stages' => $pages
                ? (clone $pages)->select('funnel_stage',DB::raw('COUNT(*) views'))->groupBy('funnel_stage')->orderByDesc('views')->get()
                : [],
            'journeys' => $this->journeys($from,$to),
            'friction' => $interactions
                ? (clone $interactions)->whereIn('event_type',['rage_click','dead_click','js_error','form_interaction'])
                    ->select('event_type',DB::raw('COUNT(*) total'))->groupBy('event_type')->get()
                : [],
            'devices' => DB::table('live_pax_sessions')
                ->whereBetween('started_at',[$from,$to])
                ->select('device_type',DB::raw('COUNT(*) total'))
                ->groupBy('device_type')
                ->orderByDesc('total')
                ->get(),
            'countries' => DB::table('live_pax_sessions')
                ->whereBetween('started_at',[$from,$to])
                ->select('country',DB::raw('COUNT(*) total'))
                ->groupBy('country')
                ->orderByDesc('total')
                ->limit(15)
                ->get(),
        ];
    }

    private function journeys($from,$to): array
    {
        if (!Schema::hasTable('live_pax_page_views')) return [];

        $sessionIds = DB::table('live_pax_sessions')
            ->whereBetween('started_at',[$from,$to])
            ->orderByDesc('started_at')
            ->limit(1000)
            ->pluck('id');

        if ($sessionIds->isEmpty()) return [];

        $rows = DB::table('live_pax_page_views')
            ->whereIn('session_id',$sessionIds)
            ->orderBy('session_id')
            ->orderBy('id')
            ->get()
            ->groupBy('session_id');

        $counts=[];

        foreach($rows as $sessionRows){
            $stages=$sessionRows->pluck('funnel_stage')->filter()->unique()->values()->all();
            $key=implode(' → ',$stages);
            if($key!=='') $counts[$key]=($counts[$key]??0)+1;
        }

        arsort($counts);

        return collect($counts)
            ->take(15)
            ->map(fn($count,$journey)=>['journey'=>$journey,'sessions'=>$count])
            ->values()
            ->all();
    }

    private function safePath(string $path): string
    {
        $path = parse_url($path,PHP_URL_PATH) ?: '/';
        return Str::limit('/'.ltrim($path,'/'),500,'');
    }

    private function emptyData(): array
    {
        return [
            'stats'=>['online'=>0,'active'=>0,'idle'=>0,'payment'=>0,'results'=>0],
            'live'=>[],
            'recorded'=>[],
            'analytics'=>[
                'top_pages'=>[],
                'stages'=>[],
                'journeys'=>[],
                'friction'=>[],
                'devices'=>[],
                'countries'=>[]
            ]
        ];
    }
}
