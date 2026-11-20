<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;

/**
 * Step G4: email the team when a Stripe payment is stuck or webhooks fail (subject shows LIVE/TEST).
 * Reads StripeAttentionService only. No Stripe call, no DB write.
 * Each problem is mailed once; money-on-hold (HIGH) is repeated every 6 hours.
 * "Already sent" marks are set only after a successful send.
 *
 * Fix 14c: the shared Laravel file cache is mostly owned by apache (755), so the
 * cron user (ec2-user) cannot write there. This service therefore uses its OWN
 * small file cache + run lock in storage/framework/stripe-alert-cache.
 */
class StripeAlertService
{
    public const PANEL_URL = 'https://crm.fareshunt.com/payment-gateway';
    public const REPEAT_HIGH_HOURS = 6;
    public const KEEP_OTHER_DAYS = 7;
    public const MAX_RECIPIENTS = 5;
    public const STORE_DIR = 'framework/stripe-alert-cache';

    private ?\Illuminate\Contracts\Cache\Repository $store = null;

    public function __construct(private StripeAttentionService $attention) {}

    /** @return array{status:string, items:int, recipients:int, subject?:string, body?:string} */
    public function run(bool $dryRun = false): array
    {
        $dir = $this->storeDir();
        if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
            Log::error('FARESHUNT_STRIPE_ALERT_STORE_UNWRITABLE');
            return ['status' => 'store_unwritable', 'items' => 0, 'recipients' => 0];
        }
        $lock = @fopen($dir.'/run.lock', 'c');
        if ($lock === false) {
            Log::error('FARESHUNT_STRIPE_ALERT_STORE_UNWRITABLE');
            return ['status' => 'store_unwritable', 'items' => 0, 'recipients' => 0];
        }
        if (!flock($lock, LOCK_EX | LOCK_NB)) {
            fclose($lock);
            return ['status' => 'already_running', 'items' => 0, 'recipients' => 0];
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
        $new = [];
        foreach ($this->attention->attention() as $row) {
            $key = 'fh_stripe_alert:attempt:'.$row['id'].':'.$row['status'].':'.($row['error'] !== '' ? 'err' : 'ok');
            if (!$this->store()->has($key)) {
                $new[] = ['key' => $key, 'ttl' => $row['level'] === 'high' ? self::REPEAT_HIGH_HOURS * 3600 : self::KEEP_OTHER_DAYS * 86400, 'row' => $row];
            }
        }

        $webhook = $this->attention->webhookHealth();
        $webhookWarning = null;
        if (!empty($webhook['warning'])) {
            $key = 'fh_stripe_alert:webhook:'.md5((string) $webhook['warning']);
            if (!$this->store()->has($key)) {
                $webhookWarning = ['key' => $key, 'ttl' => self::REPEAT_HIGH_HOURS * 3600, 'text' => (string) $webhook['warning'], 'health' => $webhook];
            }
        }

        if (!$new && !$webhookWarning) {
            return ['status' => 'nothing_new', 'items' => 0, 'recipients' => 0];
        }

        $recipients = $this->recipients();
        [$subject, $body] = $this->compose($new, $webhookWarning);
        $count = count($new) + ($webhookWarning ? 1 : 0);

        if ($dryRun) {
            return ['status' => 'dry_run', 'items' => $count, 'recipients' => count($recipients), 'subject' => $subject, 'body' => $body];
        }
        if (!$recipients) {
            Log::warning('FARESHUNT_STRIPE_ALERT_NO_RECIPIENT', ['items' => $count]);
            return ['status' => 'no_recipient', 'items' => $count, 'recipients' => 0];
        }
        if (!$this->mailOn()) {
            Log::warning('FARESHUNT_STRIPE_ALERT_SKIPPED_MAIL_OFF', ['items' => $count]);
            return ['status' => 'mail_off', 'items' => $count, 'recipients' => count($recipients)];
        }

        try {
            Mail::raw($body, function ($m) use ($recipients, $subject) {
                $m->to($recipients)->subject($subject);
            });
        } catch (\Throwable $e) {
            Log::error('FARESHUNT_STRIPE_ALERT_FAILED', ['items' => $count, 'error' => get_class($e)]);
            return ['status' => 'send_failed', 'items' => $count, 'recipients' => count($recipients)];
        }

        try {
            foreach ($new as $n) {
                $this->store()->put($n['key'], 1, $n['ttl']);
            }
            if ($webhookWarning) {
                $this->store()->put($webhookWarning['key'], 1, $webhookWarning['ttl']);
            }
        } catch (\Throwable $e) {
            Log::error('FARESHUNT_STRIPE_ALERT_MARK_FAILED', ['error' => get_class($e)]);
        }
        Log::info('FARESHUNT_STRIPE_ALERT_SENT', ['items' => $count, 'recipients' => count($recipients)]);

        return ['status' => 'sent', 'items' => $count, 'recipients' => count($recipients)];
    }

    private function storeDir(): string
    {
        return rtrim((string) config('stripe_checkout.alert_store_path', storage_path(self::STORE_DIR)), '/');
    }

    private function store(): \Illuminate\Contracts\Cache\Repository
    {
        return $this->store ??= new \Illuminate\Cache\Repository(new \Illuminate\Cache\FileStore(app('files'), $this->storeDir()));
    }

    /** Site contact email (settings.content_emailid) + STRIPE_ALERT_EMAILS, valid and unique. */
    public function recipients(): array
    {
        $list = [];
        try {
            if (Schema::hasTable('settings')) {
                $list[] = (string) DB::table('settings')->where('name', 'content_emailid')->orderByDesc('id')->value('value');
            }
        } catch (\Throwable $e) {
            // ignore; .env list still works
        }
        foreach (explode(',', (string) config('stripe_checkout.alert_emails', '')) as $e) {
            $list[] = $e;
        }

        $out = [];
        foreach ($list as $e) {
            $e = strtolower(trim($e));
            if ($e !== '' && filter_var($e, FILTER_VALIDATE_EMAIL) && !in_array($e, $out, true)) {
                $out[] = $e;
            }
        }

        return array_slice($out, 0, self::MAX_RECIPIENTS);
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

    private function compose(array $new, ?array $webhookWarning): array
    {
        $high = count(array_filter($new, fn ($n) => $n['row']['level'] === 'high'));
        $count = count($new) + ($webhookWarning ? 1 : 0);
        $subject = '[FaresHunt Stripe '.strtoupper(app(StripeTestPaymentService::class)->mode()).'] '.$count.' item(s) need attention'.($high ? ' - '.$high.' payment(s) on hold' : '');

        $lines = [
            'FaresHunt - Stripe '.strtoupper(app(StripeTestPaymentService::class)->mode()).' payments need attention',
            'Time (server, UTC): '.now()->format('Y-m-d H:i'),
            '',
        ];
        foreach ($new as $n) {
            $r = $n['row'];
            $lines[] = '['.strtoupper($r['level']).'] Attempt #'.$r['id'].' - '.$r['status'].' - '.$r['currency'].' '.$r['amount'].' - waiting '.$r['age']
                .($r['booking_id'] ? ' - booking #'.$r['booking_id'] : '');
            $lines[] = '  Why: '.$r['reason'];
            $lines[] = '  Do: '.$r['action'];
            if ($r['error'] !== '') {
                $lines[] = '  Error: '.$r['error'];
            }
            $lines[] = '';
        }
        if ($webhookWarning) {
            $h = $webhookWarning['health'];
            $lines[] = '[WEBHOOK] '.$webhookWarning['text'];
            $lines[] = '  Last 24h: '.(int) $h['day_total'].' events, '.(int) $h['day_failed'].' failed, '.(int) $h['stuck_received'].' stuck. Last received: '.($h['last_received_at'] ?? 'never');
            $lines[] = '';
        }
        $lines[] = 'Open the panel (Super Admin): '.self::PANEL_URL;
        $lines[] = 'Money-on-hold alerts repeat every '.self::REPEAT_HIGH_HOURS.' hours until resolved. No card data or keys are included in this email.';

        return [$subject, implode("\n", $lines)];
    }
}
