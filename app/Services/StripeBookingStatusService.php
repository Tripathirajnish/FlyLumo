<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Read-only Stripe status for one CRM booking (Step G2).
 * Never returns secrets, card data or full Stripe IDs. Any error -> [] (card hidden).
 */
class StripeBookingStatusService
{
    public const MAX_ROWS = 5;

    public function forBooking(int $bookingId): array
    {
        if ($bookingId <= 0) {
            return [];
        }

        try {
            if (!Schema::hasTable('stripe_payment_attempts')) {
                return [];
            }

            return DB::table('stripe_payment_attempts')
                ->where('booking_id', $bookingId)
                ->orderByDesc('id')
                ->limit(self::MAX_ROWS)
                ->get(['id', 'flow', 'amount_cents', 'currency', 'status', 'booking_status', 'last_error_code',
                    'stripe_payment_intent_id', 'authorized_at', 'captured_at', 'cancelled_at', 'created_at'])
                ->map(fn ($r) => [
                    'id' => (int) $r->id,
                    'flow' => (string) $r->flow,
                    'amount' => number_format(((int) $r->amount_cents) / 100, 2, '.', ','),
                    'currency' => strtoupper((string) $r->currency),
                    'status' => (string) $r->status,
                    'booking_status' => (string) ($r->booking_status ?? ''),
                    'error' => (string) ($r->last_error_code ?? ''),
                    'intent_tail' => $r->stripe_payment_intent_id ? '...'.substr((string) $r->stripe_payment_intent_id, -6) : '',
                    'authorized_at' => $r->authorized_at,
                    'captured_at' => $r->captured_at,
                    'cancelled_at' => $r->cancelled_at,
                    'created_at' => $r->created_at,
                ])
                ->all();
        } catch (\Throwable $e) {
            return [];
        }
    }
}
