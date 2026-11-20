<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Step G3 (read-only): Stripe payments that need a human, and webhook health.
 * No Stripe call, no DB write. Any error -> safe empty result (never breaks the CRM page).
 */
class StripeAttentionService
{
    /** created but no Stripe session after this many minutes */
    public const CREATED_STUCK_MIN = 10;
    /** pending longer than the Stripe session life (60) + grace -> expiry webhook probably missed */
    public const PENDING_STUCK_MIN = 75;
    /** money on hold but booking not finished after this many minutes */
    public const AUTHORIZED_WAIT_MIN = 15;
    /** webhook event still "received" (not processed) after this many minutes */
    public const EVENT_STUCK_MIN = 5;
    public const MAX_ROWS = 50;

    public function attention(): array
    {
        try {
            if (!Schema::hasTable('stripe_payment_attempts')) {
                return [];
            }
            $now = now();
            $rows = DB::table('stripe_payment_attempts')
                ->whereIn('status', ['created', 'pending', 'authorized'])
                ->where(function ($q) use ($now) {
                    $q->where(fn ($w) => $w->where('status', 'created')->where('created_at', '<', $now->copy()->subMinutes(self::CREATED_STUCK_MIN)))
                      ->orWhere(fn ($w) => $w->where('status', 'pending')->where('created_at', '<', $now->copy()->subMinutes(self::PENDING_STUCK_MIN)))
                      ->orWhere(fn ($w) => $w->where('status', 'authorized')->where(fn ($a) => $a->where('authorized_at', '<', $now->copy()->subMinutes(self::AUTHORIZED_WAIT_MIN))
                            ->orWhere(fn ($b) => $b->whereNull('authorized_at')->where('updated_at', '<', $now->copy()->subMinutes(self::AUTHORIZED_WAIT_MIN)))))
                      ->orWhereNotNull('last_error_code');
                })
                ->orderByRaw("CASE status WHEN 'authorized' THEN 0 WHEN 'pending' THEN 1 ELSE 2 END")
                ->orderBy('id')
                ->limit(self::MAX_ROWS)
                ->get(['id', 'booking_id', 'flow', 'amount_cents', 'currency', 'status', 'booking_status', 'last_error_code', 'created_at', 'authorized_at', 'updated_at']);

            return $rows->map(function ($r) use ($now) {
                [$level, $reason, $action] = ($r->status === 'authorized' && $r->booking_status === 'booked') ? ['medium', 'Booking is done; the payment is still only on hold (not captured).', 'Open the booking and press Capture payment. A card hold expires after about 7 days.'] : match ((string) $r->status) {
                    'authorized' => ['high', 'Money is on hold but the booking is not finished.', 'Check the supplier booking, then capture or cancel the hold. A card hold expires after about 7 days.'],
                    'pending' => ['medium', 'Customer opened Stripe checkout long ago; no result received.', 'Check the Stripe dashboard for this payment and the webhook health below.'],
                    default => ['low', 'Stripe checkout session was never created.', 'Usually safe to ignore; the customer saw an error. Check the error code.'],
                };
                $since = $r->status === 'authorized' ? ($r->authorized_at ?? $r->updated_at) : $r->created_at;
                $minutes = $since ? max(0, (int) floor(($now->getTimestamp() - strtotime((string) $since)) / 60)) : null;
                if (!empty($r->last_error_code)) {
                    if ($r->status === 'created') {
                        $reason = 'Stripe checkout could not be started.';
                    } elseif ($r->status === 'pending' && $minutes !== null && $minutes < self::PENDING_STUCK_MIN) {
                        $reason = 'Stripe reported a problem for this payment.';
                    }
                }

                return [
                    'id' => (int) $r->id,
                    'booking_id' => $r->booking_id ? (int) $r->booking_id : null,
                    'flow' => (string) $r->flow,
                    'amount' => number_format(((int) $r->amount_cents) / 100, 2, '.', ','),
                    'currency' => strtoupper((string) $r->currency),
                    'status' => (string) $r->status,
                    'booking_status' => (string) ($r->booking_status ?? ''),
                    'error' => (string) ($r->last_error_code ?? ''),
                    'level' => $level,
                    'reason' => $reason,
                    'action' => $action,
                    'age' => $minutes === null ? '-' : ($minutes >= 1440 ? floor($minutes / 1440).'d '.floor(($minutes % 1440) / 60).'h' : ($minutes >= 60 ? floor($minutes / 60).'h '.($minutes % 60).'m' : $minutes.'m')),
                ];
            })->all();
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function webhookHealth(): array
    {
        $out = [
            'available' => false,
            'last_received_at' => null,
            'last_processed_at' => null,
            'day_total' => 0,
            'day_processed' => 0,
            'day_failed' => 0,
            'stuck_received' => 0,
            'open_attempts' => 0,
            'warning' => null,
            'problems' => [],
        ];

        try {
            if (!Schema::hasTable('stripe_webhook_events')) {
                return $out;
            }
            $now = now();
            $dayAgo = $now->copy()->subDay();
            $t = fn () => DB::table('stripe_webhook_events');

            $out['available'] = true;
            $out['last_received_at'] = $t()->max('created_at');
            $out['last_processed_at'] = $t()->max('processed_at');
            $out['day_total'] = (int) $t()->where('created_at', '>=', $dayAgo)->count();
            $out['day_processed'] = (int) $t()->where('created_at', '>=', $dayAgo)->where('processing_status', 'processed')->count();
            $out['day_failed'] = (int) $t()->where('created_at', '>=', $dayAgo)->where('processing_status', 'failed')->count();
            $out['stuck_received'] = (int) $t()->where('processing_status', 'received')->where('updated_at', '<', $now->copy()->subMinutes(self::EVENT_STUCK_MIN))->count();

            $out['problems'] = $t()
                ->where(fn ($q) => $q->where('processing_status', 'failed')
                    ->orWhere(fn ($w) => $w->where('processing_status', 'received')->where('updated_at', '<', $now->copy()->subMinutes(self::EVENT_STUCK_MIN))))
                ->orderByDesc('id')->limit(10)
                ->get(['stripe_event_id', 'event_type', 'processing_status', 'attempt_count', 'last_error_code', 'created_at'])
                ->map(fn ($e) => [
                    'event' => '...'.substr((string) $e->stripe_event_id, -6),
                    'type' => (string) $e->event_type,
                    'status' => (string) $e->processing_status,
                    'attempts' => (int) $e->attempt_count,
                    'error' => (string) ($e->last_error_code ?? ''),
                    'created_at' => $e->created_at,
                ])->all();

            if (Schema::hasTable('stripe_payment_attempts')) {
                $out['open_attempts'] = (int) DB::table('stripe_payment_attempts')
                    ->whereIn('status', ['pending', 'authorized'])
                    ->where('created_at', '<', $now->copy()->subMinutes(self::PENDING_STUCK_MIN))->count();
            }

            if ($out['day_failed'] > 0 || $out['stuck_received'] > 0) {
                $out['warning'] = 'Some webhook events failed or are stuck. Stripe retries failed events automatically; check the list below.';
            } elseif ($out['open_attempts'] > 0 && $out['day_total'] === 0) {
                $out['warning'] = 'Payments are waiting but no webhook event arrived in the last 24 hours. Check the webhook endpoint and secret.';
            }
        } catch (\Throwable $e) {
            $out['available'] = false;
        }

        return $out;
    }
}
