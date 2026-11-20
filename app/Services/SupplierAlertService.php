<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;

/**
 * L5-5b (17 Nov 2026): email the team when the flight supplier is down.
 * Reads ONLY the meta-monitor results (meta_monitor_checks) - no supplier call.
 *  - DOWN: the last 3 checks of a source are all "error" (about 15 min) -> one email.
 *  - Still down: a reminder every 6 hours.
 *  - RECOVERED: the newest check is healthy/warning again -> one email.
 * Recipients, the mail ON/OFF switch and the mailer are the same as the Stripe alerts.
 * Own small file store + run lock (same pattern as StripeAlertService, Fix 14c).
 * "Sent" marks are written only after a successful send.
 */
class SupplierAlertService
{
    public const DOWN_AFTER = 3;
    public const REMIND_HOURS = 6;
    public const FRESH_MINUTES = 30;
    public const STORE_DIR = 'framework/supplier-alert-cache';

    private ?\Illuminate\Contracts\Cache\Repository $store = null;

    /** @return array{status:string, sent:int, details:array} */
    public function run(bool $dryRun = false): array
    {
        $dir = $this->storeDir();
        if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
            Log::error('FARESHUNT_SUPPLIER_ALERT_STORE_UNWRITABLE');
            return ['status' => 'store_unwritable', 'sent' => 0, 'details' => []];
        }
        $lock = @fopen($dir.'/run.lock', 'c');
        if ($lock === false) {
            Log::error('FARESHUNT_SUPPLIER_ALERT_STORE_UNWRITABLE');
            return ['status' => 'store_unwritable', 'sent' => 0, 'details' => []];
        }
        if (!flock($lock, LOCK_EX | LOCK_NB)) {
            fclose($lock);
            return ['status' => 'already_running', 'sent' => 0, 'details' => []];
        }

        try {
            return $this->runLocked($dryRun);
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }

    private function runLocked(bool $dryRun): array
    {
        if (!Schema::hasTable('meta_monitor_sources') || !Schema::hasTable('meta_monitor_checks')) {
            return ['status' => 'no_monitor_tables', 'sent' => 0, 'details' => []];
        }

        $sent = 0;
        $details = [];

        foreach (DB::table('meta_monitor_sources')->where('is_active', 1)->orderBy('id')->get(['id', 'name']) as $source) {
            $checks = DB::table('meta_monitor_checks')->where('source_id', $source->id)
                ->orderByDesc('id')->limit(50)->get(['id', 'status', 'error_message', 'checked_at']);
            if ($checks->isEmpty()) {
                continue;
            }

            $newest = $checks->first();
            $fresh = $newest->checked_at && strtotime((string) $newest->checked_at) >= time() - self::FRESH_MINUTES * 60;

            $streak = [];
            foreach ($checks as $c) {
                if ($c->status !== 'error') {
                    break;
                }
                $streak[] = $c;
            }

            $downKey = 'supplier_alert:down:'.$source->id;
            $remindKey = 'supplier_alert:remind:'.$source->id;
            $isDown = $fresh && count($streak) >= self::DOWN_AFTER;
            $isUp = in_array($newest->status, ['healthy', 'warning'], true);

            if ($isDown && (!$this->store()->has($downKey) || !$this->store()->has($remindKey))) {
                $kind = $this->store()->has($downKey) ? 'STILL DOWN' : 'DOWN';
                [$subject, $body] = $this->composeDown($source, $streak, $kind);
                $details[] = $kind.' source #'.$source->id;
                if (!$dryRun && $this->deliver($subject, $body)) {
                    $sent++;
                    $this->store()->forever($downKey, (string) end($streak)->checked_at);
                    $this->store()->put($remindKey, 1, self::REMIND_HOURS * 3600);
                }
            } elseif ($isUp && $this->store()->has($downKey)) {
                $since = (string) $this->store()->get($downKey);
                [$subject, $body] = $this->composeUp($source, $newest, $since);
                $details[] = 'RECOVERED source #'.$source->id;
                if (!$dryRun && $this->deliver($subject, $body)) {
                    $sent++;
                    $this->store()->forget($downKey);
                    $this->store()->forget($remindKey);
                }
            }
        }

        return ['status' => $dryRun ? 'dry_run' : ($details ? 'checked' : 'nothing_new'), 'sent' => $sent, 'details' => $details];
    }

    /** true only when the email really went out. */
    protected function deliver(string $subject, string $body): bool
    {
        $recipients = app(StripeAlertService::class)->recipients();
        if (!$recipients) {
            Log::warning('FARESHUNT_SUPPLIER_ALERT_NO_RECIPIENT');
            return false;
        }
        if (!$this->mailOn()) {
            Log::warning('FARESHUNT_SUPPLIER_ALERT_SKIPPED_MAIL_OFF');
            return false;
        }
        try {
            Mail::raw($body, function ($m) use ($recipients, $subject) {
                $m->to($recipients)->subject($subject);
            });
        } catch (\Throwable $e) {
            Log::error('FARESHUNT_SUPPLIER_ALERT_FAILED', ['error' => get_class($e)]);
            return false;
        }
        Log::info('FARESHUNT_SUPPLIER_ALERT_SENT', ['subject' => $subject, 'recipients' => count($recipients)]);

        return true;
    }

    private function composeDown(object $source, array $streak, string $kind): array
    {
        $first = end($streak);
        $subject = '[FaresHunt] Flight supplier '.$kind.' - searches failing since '.$this->both((string) $first->checked_at);
        $lines = [
            'FaresHunt - flight supplier is not answering ('.$kind.')',
            'Monitor: '.$source->name.' (source #'.$source->id.')',
            'Failing since: '.$this->both((string) $first->checked_at).'  ('.count($streak).' checks in a row)',
            '',
            'Last checks:',
        ];
        foreach (array_slice($streak, 0, 5) as $c) {
            $lines[] = '  '.$this->both((string) $c->checked_at).' - '.mb_substr((string) ($c->error_message ?: 'error'), 0, 160);
        }
        $lines[] = '';
        $lines[] = 'What customers see: "Our airline partner is not responding right now. Please try your search again in a minute."';
        $lines[] = 'What to do: nothing on our server; if it lasts long, contact the supplier (city.travel) with these times.';
        $lines[] = 'You get one more email when it is back, and a reminder every '.self::REMIND_HOURS.' hours while it stays down.';
        $lines[] = 'Details: CRM > Meta Monitor.';

        return [$subject, implode("\n", $lines)];
    }

    private function composeUp(object $source, object $newest, string $since): array
    {
        $subject = '[FaresHunt] Flight supplier RECOVERED - searches working again';
        $lines = [
            'FaresHunt - flight supplier is answering again',
            'Monitor: '.$source->name.' (source #'.$source->id.')',
            'Down since: '.($since !== '' ? $this->both($since) : 'unknown'),
            'Back at:    '.$this->both((string) $newest->checked_at).' (check status: '.$newest->status.')',
        ];

        return [$subject, implode("\n", $lines)];
    }

    /** "2026-11-16 12:01 UTC / 17:31 IST" */
    private function both(string $utc): string
    {
        $t = strtotime($utc.' UTC');
        if ($t === false) {
            return $utc;
        }

        return gmdate('Y-m-d H:i', $t).' UTC / '.gmdate('H:i', $t + 19800).' IST';
    }

    /** Same switch the site uses for its own mails (settings.is_mail). */
    private function mailOn(): bool
    {
        try {
            return strtoupper(trim((string) DB::table('settings')->where('name', 'is_mail')->orderByDesc('id')->value('value'))) === 'ON';
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function storeDir(): string
    {
        return rtrim((string) config('supplier_alert.store_path', storage_path(self::STORE_DIR)), '/');
    }

    private function store(): \Illuminate\Contracts\Cache\Repository
    {
        return $this->store ??= new \Illuminate\Cache\Repository(new \Illuminate\Cache\FileStore(app('files'), $this->storeDir()));
    }
}
