<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;

/**
 * Daily health report (17 Nov 2026). One email per day about the previous UTC day.
 * READ-ONLY: reads the Laravel log of that day + bookings, stripe_payment_attempts,
 * meta_monitor_checks. No supplier call, no Stripe call, no DB write.
 * Recipients / mail switch = the same as the Stripe and supplier alerts.
 * "Site inactive" (planned downtime): create storage/framework/fh-site-inactive -
 * the report then says so instead of raising "no searches".
 */
class DailyHealthReportService
{
    public const STORE_DIR = 'framework/supplier-alert-cache';   // shared with the supplier alert (ec2-user:apache 2775)
    public const ERROR_LIMIT = 5;
    public const SUPPLIER_FAIL_LIMIT = 3;
    public const ROBOT_ERROR_LIMIT = 3;
    public const BACKUP_MAX_AGE_HOURS = 26;   // DB backup runs daily 21:30 UTC (17 Nov 2026)
    public const CORE_FIRST_FAIL_LIMIT = 5;   // FareLeaders Core first tries failed in a day (18 Nov 2026)
    public const CORE_SLOW_MS = 20000;        // a failed first try longer than this = customer waited
    public const FARE_ABSORB_LIMIT_CENTS = 10000;   // supplier above shown price, total per day ($100)

    /** @return array{status:string, attention:int, subject?:string, body?:string} */
    public function run(?string $date = null, bool $dryRun = false, bool $force = false): array
    {
        $day = $date ? Carbon::parse($date, 'UTC')->startOfDay() : now('UTC')->subDay()->startOfDay();
        $mark = $this->storeDir().'/daily-report-'.$day->format('Y-m-d').'.sent';

        if (!$dryRun && !$force && is_file($mark)) {
            return ['status' => 'already_sent', 'attention' => 0];
        }

        $m = $this->collect($day);
        [$subject, $body, $attention] = $this->compose($day, $m);

        if ($dryRun) {
            return ['status' => 'dry_run', 'attention' => $attention, 'subject' => $subject, 'body' => $body];
        }
        if (!$this->deliver($subject, $body)) {
            return ['status' => 'not_sent', 'attention' => $attention, 'subject' => $subject];
        }
        @file_put_contents($mark, now('UTC')->toIso8601String());

        return ['status' => 'sent', 'attention' => $attention, 'subject' => $subject];
    }

    public function collect(Carbon $day): array
    {
        $from = $day->copy();
        $to = $day->copy()->addDay();
        $m = [
            'log_found' => false, 'inactive' => is_file(storage_path('framework/fh-site-inactive')),
            'searches_all' => 0, 'searches_real' => 0, 'search_fail' => 0, 'booking_page' => 0,
            'supplier_unavailable' => 0, 'soap_retry' => 0, 'soap_failed' => 0, 'core_unavailable' => 0,
            'errors' => 0, 'top_errors' => [], 'memory' => 0, 'mismatch' => 0, 'quote_failed' => 0, 'quote_changed' => 0,
            'manual_review_log' => 0, 'save_failed' => 0, 'deeplink_rejected' => 0,
            // 18 Nov 2026: FareLeaders Core retries + fares accepted at a new supplier price
            'core_retry_ok' => 0, 'core_first_fail' => 0, 'core_retry_fail' => 0, 'core_slow' => 0, 'core_gz' => 0,
            'fare_pay' => ['n' => 0, 'up' => 0, 'down' => 0, 'max' => 0, 'codes' => []],
            'fare_book' => ['n' => 0, 'up' => 0, 'down' => 0, 'max' => 0, 'codes' => []],
            'robot' => [], 'attempts' => [], 'attempts_problem' => [], 'bookings' => [],
            'backup' => $this->backupStatus(),
        ];

        // robot search codes of that day (excluded from "real searches")
        $robotCodes = [];
        try {
            if (Schema::hasTable('meta_monitor_checks')) {
                foreach (DB::table('meta_monitor_checks')->where('checked_at', '>=', $from)->where('checked_at', '<', $to)
                    ->get(['status', 'search_code']) as $r) {
                    $m['robot'][$r->status] = ($m['robot'][$r->status] ?? 0) + 1;
                    if ($r->search_code) {
                        $robotCodes[$r->search_code] = true;
                    }
                }
            }
        } catch (\Throwable $e) {
            $m['robot']['read_error'] = 1;
        }

        $file = storage_path('logs/laravel-'.$day->format('Y-m-d').'.log');
        $h = is_readable($file) ? @fopen($file, 'r') : false;
        if ($h) {
            $m['log_found'] = true;
            $errs = [];
            while (($line = fgets($h)) !== false) {
                if (!preg_match('/^\[\d{4}-\d\d-\d\d [\d:]{8}\] \w+\.([A-Z]+): (.*)$/', $line, $x)) {
                    continue;
                }
                [$level, $msg] = [$x[1], $x[2]];
                if (str_starts_with($msg, 'FARESHUNT_LISTING_PERFORMANCE')) {
                    $m['searches_all']++;
                    $code = preg_match('/"search_code":"([A-Za-z0-9_-]+)"/', $msg, $c) ? $c[1] : '';
                    if ($code === '' || !isset($robotCodes[$code])) {
                        $m['searches_real']++;
                        if (preg_match('/"result_count":(null|0)[,}]/', $msg)) {
                            $m['search_fail']++;
                        }
                    }
                    continue;
                }
                foreach ([
                    'FARESHUNT_PASSENGER_PAGE_PERFORMANCE' => 'booking_page', 'FARESHUNT_SUPPLIER_UNAVAILABLE' => 'supplier_unavailable',
                    'FARESHUNT_SOAP_RETRY' => 'soap_retry', 'FARESHUNT_SOAP_ATTEMPT_FAILED' => 'soap_failed',
                    'FL_CORE_SUPPLIER_UNAVAILABLE' => 'core_unavailable', 'FARESHUNT_STRIPE_SUPPLIER_BREAKDOWN_MISMATCH' => 'mismatch',
                    'FARESHUNT_STRIPE_TEST_QUOTE_FAILED' => 'quote_failed', 'FARESHUNT_STRIPE_TEST_QUOTE_CHANGED' => 'quote_changed',
                    'FARESHUNT_STRIPE_BOOKING_MANUAL_REVIEW' => 'manual_review_log', 'FARESHUNT_STRIPE_BOOKING_SAVE_FAILED' => 'save_failed',
                    'FARESHUNT_FARELEADER_DEEPLINK_REJECTED' => 'deeplink_rejected',
                ] as $needle => $key) {
                    if (str_starts_with($msg, $needle)) {
                        $m[$key]++;
                    }
                }
                if (str_starts_with($msg, 'FL_CORE_SOAP_RETRY')) {
                    $m['core_retry_ok']++;
                } elseif (str_starts_with($msg, 'FL_CORE_SOAP_ATTEMPT_FAILED')) {
                    $j = $this->json($msg);
                    if ((int) ($j['attempt'] ?? 0) === 1) {
                        $m['core_first_fail']++;
                        if ((int) ($j['ms'] ?? 0) > self::CORE_SLOW_MS) {
                            $m['core_slow']++;
                        }
                        if (str_contains((string) ($j['error'] ?? ''), 'gzdecode')) {
                            $m['core_gz']++;
                        }
                    } else {
                        $m['core_retry_fail']++;
                    }
                } elseif (str_starts_with($msg, 'FARESHUNT_STRIPE_FARE_CHANGED_ACCEPTED') || str_starts_with($msg, 'FARESHUNT_STRIPE_BOOKING_FARE_CHANGED_ACCEPTED')) {
                    $k = str_starts_with($msg, 'FARESHUNT_STRIPE_BOOKING_') ? 'fare_book' : 'fare_pay';
                    $j = $this->json($msg);
                    $diff = (int) ($j['diff_cents'] ?? 0);
                    $m[$k]['n']++;
                    $m[$k][$diff >= 0 ? 'up' : 'down'] += abs($diff);
                    $m[$k]['max'] = max($m[$k]['max'], $diff);
                    $m[$k]['codes'][(string) ($j['search_code'] ?? ('a'.($j['attempt_id'] ?? '?')))] = true;
                }
                if (str_contains($msg, 'Allowed memory size')) {
                    $m['memory']++;
                }
                if ($level === 'ERROR' || $level === 'CRITICAL' || $level === 'ALERT' || $level === 'EMERGENCY') {
                    $m['errors']++;
                    $short = preg_replace(['/\{.*$/', '/ at \/.*$/', '/[0-9a-f]{16,}/', '/\d+/'], ['', '', '#', '#'], $msg);
                    $short = mb_substr(trim((string) $short), 0, 110);
                    $errs[$short] = ($errs[$short] ?? 0) + 1;
                }
            }
            fclose($h);
            arsort($errs);
            $m['top_errors'] = array_slice($errs, 0, 3, true);
        }

        try {
            if (Schema::hasTable('stripe_payment_attempts')) {
                foreach (DB::table('stripe_payment_attempts')->where('created_at', '>=', $from)->where('created_at', '<', $to)
                    ->get(['id', 'status', 'booking_status', 'last_error_code']) as $a) {
                    $m['attempts'][$a->status] = ($m['attempts'][$a->status] ?? 0) + 1;
                }
                foreach (DB::table('stripe_payment_attempts')->where('updated_at', '>=', $from)->where('updated_at', '<', $to)
                    ->where(fn ($q) => $q->where('booking_status', 'manual_review')->orWhereIn('status', ['authorized']))
                    ->orderBy('id')->limit(10)->get(['id', 'status', 'booking_status', 'last_error_code']) as $a) {
                    $m['attempts_problem'][] = '#'.$a->id.' '.$a->status.'/'.($a->booking_status ?: '-').($a->last_error_code ? ' ('.$a->last_error_code.')' : '');
                }
            }
        } catch (\Throwable $e) {
            $m['attempts']['read_error'] = 1;
        }

        try {
            foreach (DB::table('bookings')->where('created_at', '>=', $from)->where('created_at', '<', $to)
                ->selectRaw('source, COUNT(*) n')->groupBy('source')->get() as $b) {
                $m['bookings'][(string) ($b->source ?: 'unknown')] = (int) $b->n;
            }
        } catch (\Throwable $e) {
            $m['bookings']['read_error'] = 1;
        }

        return $m;
    }

    /** The JSON context after the log message name ("NAME {...}"); [] if none. */
    private function json(string $msg): array
    {
        $p = strpos($msg, '{');
        $j = $p === false ? null : json_decode(rtrim(substr($msg, $p)), true);
        if (!is_array($j) && $p !== false && preg_match('/^(\{.*\})\s*\[\]\s*$/', rtrim(substr($msg, $p)), $x)) {
            $j = json_decode($x[1], true);
        }

        return is_array($j) ? $j : [];
    }

    /**
     * DB backup status written by /usr/local/sbin/fareshunt-db-backup.sh (one line, no secrets):
     * "2026-11-16 22:39:57 UTC OK db-....sql.gz 375278764 bytes 96s s3 off kept=2"
     */
    public function backupStatus(): array
    {
        $path = (string) config('daily_report.backup_status_path', '/var/lib/fareshunt-backup/status');
        $line = is_readable($path) ? trim((string) @file_get_contents($path)) : '';
        if ($line === '') {
            return ['found' => false, 'ok' => false, 'age_h' => null, 'line' => 'no status file ('.$path.')'];
        }
        $ts = preg_match('/^(\d{4}-\d\d-\d\d \d\d:\d\d:\d\d) UTC /', $line, $x) ? strtotime($x[1].' UTC') : false;
        $age = $ts ? round((time() - $ts) / 3600, 1) : null;

        return [
            'found' => true,
            'ok' => str_contains($line, ' UTC OK '),
            'age_h' => $age,
            'line' => mb_substr($line, 0, 200),
        ];
    }

    /** @return array{0:string,1:string,2:int} */
    public function compose(Carbon $day, array $m): array
    {
        $att = [];
        $robotErr = (int) ($m['robot']['error'] ?? 0);
        $supplierFail = $m['supplier_unavailable'] + $m['core_unavailable'];

        if (!$m['log_found']) {
            $att[] = 'No Laravel log file for this day (logging broken or site down?).';
        }
        if (!$m['inactive'] && $m['log_found'] && $m['searches_real'] === 0) {
            $att[] = 'No real customer searches at all. Is the site / FareLeaders traffic live? (If this is planned, create storage/framework/fh-site-inactive.)';
        }
        if ($m['errors'] > self::ERROR_LIMIT) {
            $att[] = $m['errors'].' errors in the log (limit '.self::ERROR_LIMIT.'). See "Top errors" below.';
        }
        if ($supplierFail >= self::SUPPLIER_FAIL_LIMIT) {
            $att[] = 'Supplier did not answer '.$supplierFail.' times (after retry).';
        }
        if ($robotErr >= self::ROBOT_ERROR_LIMIT) {
            $att[] = 'Monitor robot saw '.$robotErr.' failed checks.';
        }
        if ($m['memory'] > 0) {
            $att[] = 'Memory errors: '.$m['memory'].' (L6 - big search sessions).';
        }
        if ($m['mismatch'] > 0) {
            $att[] = 'Supplier fare breakdown mismatch: '.$m['mismatch'].' (L1 - payment was allowed; check the numbers in the log).';
        }
        if ($m['save_failed'] > 0) {
            $att[] = 'BOOKING SAVE FAILED after the supplier booked: '.$m['save_failed'].' - a human must finish it NOW.';
        }
        $bk = $m['backup'] ?? ['found' => false, 'ok' => false, 'age_h' => null, 'line' => 'unknown'];
        if (!$bk['found'] || !$bk['ok']) {
            $att[] = 'DATABASE BACKUP problem: '.$bk['line'];
        } elseif ($bk['age_h'] === null || $bk['age_h'] > self::BACKUP_MAX_AGE_HOURS) {
            $att[] = 'DATABASE BACKUP is old: last good backup '.($bk['age_h'] ?? '?').' h ago (limit '.self::BACKUP_MAX_AGE_HOURS.' h).';
        }
        $coreFirst = (int) ($m['core_first_fail'] ?? 0);
        if ($coreFirst >= self::CORE_FIRST_FAIL_LIMIT) {
            $att[] = 'FareLeaders Core: '.$coreFirst.' first tries failed ('.($m['core_slow'] ?? 0).' slower than '.(self::CORE_SLOW_MS / 1000).' s, '
                .($m['core_gz'] ?? 0).' gzdecode); retry saved '.($m['core_retry_ok'] ?? 0).'. If this repeats, do the 20 s first-try limit fix.';
        }
        $fp = $m['fare_pay'] ?? ['n' => 0, 'up' => 0, 'down' => 0, 'max' => 0, 'codes' => []];
        $fb = $m['fare_book'] ?? ['n' => 0, 'up' => 0, 'down' => 0, 'max' => 0, 'codes' => []];
        if ($fp['up'] + $fb['up'] >= self::FARE_ABSORB_LIMIT_CENTS) {
            $att[] = 'Supplier price above the shown price, accepted: +$'.number_format(($fp['up'] + $fb['up']) / 100, 2)
                .' in total today (limit $'.number_format(self::FARE_ABSORB_LIMIT_CENTS / 100, 0).'). See "Fare changes" below.';
        }
        if ($m['attempts_problem']) {
            $att[] = 'Payments needing a human: '.implode(', ', $m['attempts_problem']).'.';
        }

        $d = $day->format('D d M Y');
        $subject = '[FaresHunt daily] '.$d.' - '.($att ? 'ATTENTION ('.count($att).')' : 'all OK')
            .' - '.$m['searches_real'].' searches, '.array_sum(array_filter($m['bookings'], 'is_int')).' bookings';

        $kv = fn (array $a) => $a ? implode(', ', array_map(fn ($k, $v) => $k.' '.$v, array_keys($a), $a)) : 'none';
        $lines = [
            'FaresHunt daily health report',
            'Day: '.$d.' (UTC 00:00-24:00 = IST '.$day->copy()->setTimezone('Asia/Kolkata')->format('d M H:i').' - '.$day->copy()->addDay()->setTimezone('Asia/Kolkata')->format('d M H:i').')',
            $m['inactive'] ? 'Site marked INACTIVE (storage/framework/fh-site-inactive exists).' : '',
            '',
        ];
        if ($att) {
            $lines[] = 'ATTENTION';
            foreach ($att as $a) {
                $lines[] = '  - '.$a;
            }
            $lines[] = '';
        }
        $lines = array_merge($lines, [
            'TRAFFIC',
            '  Real customer searches: '.$m['searches_real'].' (no results / failed: '.$m['search_fail'].')   robot searches: '.($m['searches_all'] - $m['searches_real']),
            '  Booking (passenger) page opened: '.$m['booking_page'].' (website flow)',
            '  FareLeaders deeplinks rejected: '.$m['deeplink_rejected'],
            '',
            'PAYMENTS AND BOOKINGS',
            '  Stripe attempts started: '.$kv($m['attempts']),
            '  Fare check before payment: no longer available '.$m['quote_failed'].', price changed '.$m['quote_changed'],
            '  Bookings created: '.$kv($m['bookings']),
            '  Booking runs sent to manual review: '.$m['manual_review_log'],
            '',
            'FARE CHANGES ACCEPTED (customer pays the shown price; supplier price differs)',
            '  At Pay: '.$fp['n'].' times ('.count($fp['codes']).' searches) - supplier higher by $'.number_format($fp['up'] / 100, 2)
                .' in total (biggest $'.number_format(max(0, $fp['max']) / 100, 2).'), lower by $'.number_format($fp['down'] / 100, 2),
            '  At booking (after payment): '.$fb['n'].' times - supplier higher by $'.number_format($fb['up'] / 100, 2)
                .', lower by $'.number_format($fb['down'] / 100, 2),
            '  (At Pay counts every Pay click, also the ones not paid in the end.)',
            '',
            'SUPPLIER',
            '  Not answering after retry: website '.$m['supplier_unavailable'].', FareLeaders Core '.$m['core_unavailable'],
            '  Website retries: '.$m['soap_retry'].'   failed tries: '.$m['soap_failed'],
            '  FareLeaders Core: failed first tries '.($m['core_first_fail'] ?? 0).' (slower than '.(self::CORE_SLOW_MS / 1000).' s: '.($m['core_slow'] ?? 0)
                .', gzdecode: '.($m['core_gz'] ?? 0).'), saved by retry '.($m['core_retry_ok'] ?? 0).', retry also failed '.($m['core_retry_fail'] ?? 0),
            '  Monitor robot checks: '.$kv($m['robot']),
            '',
            'DATABASE BACKUP',
            '  '.(($bk['ok'] ?? false) ? 'OK, '.$bk['age_h'].' h ago' : 'NOT OK').' - '.$bk['line'],
            '',
            'ERRORS',
            '  Errors in the log: '.$m['errors'],
        ]);
        foreach ($m['top_errors'] as $msg => $n) {
            $lines[] = '    '.$n.'x '.$msg;
        }
        $lines[] = '';
        $lines[] = 'Not covered: Apache-level 504/timeouts (not in the Laravel log). Report is read-only; no card data or keys.';

        return [$subject, implode("\n", array_filter($lines, fn ($l) => $l !== null)), count($att)];
    }

    protected function deliver(string $subject, string $body): bool
    {
        $recipients = app(StripeAlertService::class)->recipients();
        if (!$recipients) {
            Log::warning('FARESHUNT_DAILY_REPORT_NO_RECIPIENT');
            return false;
        }
        try {
            $on = strtoupper(trim((string) DB::table('settings')->where('name', 'is_mail')->orderByDesc('id')->value('value'))) === 'ON';
        } catch (\Throwable $e) {
            $on = false;
        }
        if (!$on) {
            Log::warning('FARESHUNT_DAILY_REPORT_SKIPPED_MAIL_OFF');
            return false;
        }
        try {
            Mail::raw($body, fn ($msg) => $msg->to($recipients)->subject($subject));
        } catch (\Throwable $e) {
            Log::error('FARESHUNT_DAILY_REPORT_FAILED', ['error' => get_class($e)]);
            return false;
        }
        Log::info('FARESHUNT_DAILY_REPORT_SENT', ['recipients' => count($recipients)]);

        return true;
    }

    private function storeDir(): string
    {
        return rtrim((string) config('daily_report.store_path', storage_path(self::STORE_DIR)), '/');
    }
}
