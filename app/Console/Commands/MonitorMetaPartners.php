<?php

namespace App\Console\Commands;

use App\Services\MetaMonitor\Adapters\FareLeadersAdapter;
use App\Services\MetaMonitor\Adapters\MetaPartnerAdapterInterface;
use GuzzleHttp\Cookie\CookieJar;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MonitorMetaPartners extends Command
{
    protected $signature = 'meta-monitor:check';

    protected $description = 'Monitor meta partner redirect and flight-listing performance';

    public function handle(): int
    {
        $sources = DB::table('meta_monitor_sources')
            ->where('is_active', 1)
            ->orderBy('id')
            ->get();

        if ($sources->isEmpty()) {
            $this->info('No active meta partners configured.');
            return self::SUCCESS;
        }

        foreach ($sources as $source) {
            $this->checkSource($source);
        }

        return self::SUCCESS;
    }

    private function resolveAdapter(object $source): ?MetaPartnerAdapterInterface
    {
        return match (strtolower((string) ($source->adapter ?? ''))) {
            'fareleaders' => app(FareLeadersAdapter::class),
            default => null,
        };
    }

    /**
     * L5-6: delete ONLY this check's own session file. Safety: file session driver only,
     * the id must come from this check's own cookie, and the file must contain this
     * check's search code. Never throws, never changes the check result.
     */
    private function forgetMonitorSession(?CookieJar $jar, ?string $searchCode): void
    {
        try {
            if (!$jar || !$searchCode || config('session.driver') !== 'file') {
                return;
            }

            $name = (string) config('session.cookie');
            $cookie = $jar->getCookieByName($name);
            $raw = $cookie ? rawurldecode((string) $cookie->getValue()) : '';

            if ($raw === '') {
                return;
            }

            if (preg_match('/^[A-Za-z0-9]{40}$/D', $raw)) {
                $id = $raw;                                   // cookie not encrypted
            } else {
                $plain = (string) app('encrypter')->decrypt($raw, false);
                $parts = explode('|', $plain, 2);             // Laravel prefix "hmac|" + session id
                $id = count($parts) === 2 ? $parts[1] : $plain;
            }

            if (!preg_match('/^[A-Za-z0-9]{40}$/D', $id)) {
                return;
            }

            $path = rtrim((string) config('session.files'), '/').'/'.$id;

            if (!is_file($path)) {
                return;
            }

            $content = (string) @file_get_contents($path);

            if ($content === '' || !str_contains($content, $searchCode)) {
                return;                                       // not this check's search - leave it
            }

            @unlink($path);
        } catch (\Throwable $e) {
            Log::warning('FARESHUNT_META_MONITOR_SESSION_CLEANUP_FAILED', ['error' => get_class($e)]);
        }
    }

    private function checkSource(object $source): void
    {
        $totalStarted = microtime(true);

        $apiMs = null;
        $listingMs = null;
        $totalMs = null;

        $httpStatus = null;
        $listingHttpStatus = null;
        $redirectOk = false;

        $finalPath = null;
        $searchCode = null;
        $errorMessage = null;

        $status = 'error';
        $requestContext = [];
        $cookieJar = null;

        try {
            $adapter = $this->resolveAdapter($source);

            if (!$adapter) {
                throw new \RuntimeException(
                    'Unsupported meta monitor adapter: ' .
                    ($source->adapter ?: 'not configured')
                );
            }

            $request = $adapter->buildRequest($source);
            $requestContext = $request['context'] ?? [];

            $method = strtoupper($request['method'] ?? 'GET');
            $apiUrl = $request['url'] ?? null;
            $query = $request['query'] ?? [];

            if (!$apiUrl) {
                throw new \RuntimeException(
                    'Meta monitor entry URL is not configured.'
                );
            }

            $cookieJar = new CookieJar();

            /*
             * Stage 1:
             * Send the partner-specific monitoring request without
             * automatically following redirects.
             */
            $apiStarted = microtime(true);

            $pending = Http::connectTimeout(5)
                ->timeout(20)
                ->withOptions([
                    'allow_redirects' => false,
                    'cookies' => $cookieJar,
                ]);

            if ($method === 'GET') {
                $apiResponse = $pending->get($apiUrl, $query);
            } elseif ($method === 'POST') {
                $apiResponse = $pending->post($apiUrl, $query);
            } else {
                throw new \RuntimeException(
                    'Unsupported monitor HTTP method: ' . $method
                );
            }

            $apiMs = (int) round(
                (microtime(true) - $apiStarted) * 1000
            );

            $httpStatus = $apiResponse->status();
            $location = $apiResponse->header('Location');

            if ($adapter->expectsRedirect()) {
                if (
                    $httpStatus >= 300 &&
                    $httpStatus < 400 &&
                    !empty($location)
                ) {
                    $redirectOk = true;
                    $finalPath = parse_url($location, PHP_URL_PATH);
                    $searchCode = $adapter->extractSearchCode($location);

                    /*
                     * Stage 2:
                     * Preserve the same cookie/session while loading
                     * the generated flight-listing page.
                     */
                    $listingStarted = microtime(true);

                    $listingResponse = Http::connectTimeout(5)
                        ->timeout(30)
                        ->withOptions([
                            'allow_redirects' => false,
                            'cookies' => $cookieJar,
                        ])
                        ->get($location);

                    $listingMs = (int) round(
                        (microtime(true) - $listingStarted) * 1000
                    );

                    $listingHttpStatus = $listingResponse->status();

                    if ($listingResponse->successful()) {
                        // L5-5a (17 Nov 2026): since L5-1 a supplier outage returns HTTP 200 with a
                        // "not responding" message instead of a 500, so check the page text as well -
                        // otherwise an outage would be counted as healthy.
                        if (str_contains((string) $listingResponse->body(), 'Our airline partner is not responding')) {
                            $errorMessage = 'Supplier not responding (listing page shows the supplier-unavailable message)';
                        } else {
                            $status = 'healthy';
                        }
                    } else {
                        $errorMessage =
                            'Listing HTTP ' . $listingHttpStatus;
                    }
                } else {
                    $errorMessage =
                        'Expected API redirect was not received. HTTP ' .
                        $httpStatus;
                }
            } else {
                $redirectOk = true;

                if ($apiResponse->successful()) {
                    $status = 'healthy';
                } else {
                    $errorMessage = 'Partner HTTP ' . $httpStatus;
                }
            }
        } catch (\Throwable $e) {
            $status = 'error';

            $errorMessage = Str::limit(
                $e->getMessage(),
                1000,
                ''
            );
        }

        $totalMs = (int) round(
            (microtime(true) - $totalStarted) * 1000
        );

        // L5-6 (17 Nov 2026): the check is done - remove the session this check created.
        // Every check uses a new cookie jar, so each one left a ~4.6 MB session with the
        // full search result that nobody uses, kept for 120 min (~24 of them at any time).
        $this->forgetMonitorSession($cookieJar, $searchCode);

        if (
            $status === 'healthy' &&
            $totalMs > (int) $source->target_ms
        ) {
            $status = 'warning';
        }

        DB::table('meta_monitor_checks')->insert([
            'source_id' => $source->id,
            'check_type' => 'listing',
            'status' => $status,
            'http_status' => $listingHttpStatus ?: $httpStatus,
            'response_ms' => $totalMs,
            'redirect_ok' => $redirectOk,
            'final_path' => $finalPath,
            'search_code' => $searchCode,
            'result_count' => null,
            'error_message' => $errorMessage,

            'context' => json_encode(array_merge(
                $requestContext,
                [
                    'adapter' => $source->adapter,
                    'api_ms' => $apiMs,
                    'listing_ms' => $listingMs,
                    'total_ms' => $totalMs,
                    'api_http_status' => $httpStatus,
                    'listing_http_status' => $listingHttpStatus,
                    'monitor_version' => 'v3-adapter',
                ]
            )),

            'checked_at' => now(),
            'created_at' => now(),
        ]);

        DB::table('meta_monitor_sources')
            ->where('id', $source->id)
            ->update([
                'status' => $status,
                'last_response_ms' => $totalMs,
                'last_http_status' =>
                    $listingHttpStatus ?: $httpStatus,
                'last_error' => $errorMessage,
                'last_checked_at' => now(),

                'last_success_at' =>
                    in_array($status, ['healthy', 'warning'], true)
                        ? now()
                        : $source->last_success_at,

                'updated_at' => now(),
            ]);

        $this->line(sprintf(
            '%s [%s] | %s | API=%sms | LISTING=%sms | TOTAL=%sms | API_HTTP=%s | LISTING_HTTP=%s | REDIRECT=%s',
            $source->name,
            $source->adapter ?: 'unknown',
            strtoupper($status),
            $apiMs ?? 0,
            $listingMs ?? 0,
            $totalMs,
            $httpStatus ?? 'ERR',
            $listingHttpStatus ?? 'ERR',
            $redirectOk ? 'OK' : 'FAIL'
        ));
    }
}
