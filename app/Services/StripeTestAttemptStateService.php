<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Stripe-only: the ONE place that defines allowed attempt status changes.
 *
 *   created    -> pending | expired | failed
 *   pending    -> authorized | expired | cancelled | failed
 *   authorized -> captured | cancelled
 *   captured   -> refunded (Step G5, full refund)
 *   refunded / cancelled / expired / failed -> (final)
 *
 * transition() is a compare-and-set: it only updates when the row is still
 * in the expected status, so two parallel requests cannot both move it.
 */
class StripeTestAttemptStateService
{
    public const ALLOWED = [
        'created' => ['pending', 'expired', 'failed'],
        'pending' => ['authorized', 'expired', 'cancelled', 'failed'],
        'authorized' => ['captured', 'cancelled'],
        'captured' => ['refunded'],
        'refunded' => [],
        'cancelled' => [],
        'expired' => [],
        'failed' => [],
    ];

    public const REUSABLE = ['created', 'pending'];

    public function canTransition(string $from, string $to): bool
    {
        return in_array($to, self::ALLOWED[$from] ?? [], true);
    }

    public function assertTransition(string $from, string $to): void
    {
        if (!$this->canTransition($from, $to)) {
            throw new RuntimeException('Stripe attempt status change not allowed.');
        }
    }

    /**
     * @return bool true when this call moved the row, false when another
     *              request already changed it (caller must re-read).
     */
    public function transition(int $attemptId, string $from, string $to, array $extra = []): bool
    {
        $this->assertTransition($from, $to);

        $allowedExtra = [
            'stripe_checkout_session_id', 'stripe_payment_intent_id',
            'booking_id', 'booking_status', 'last_error_code',
            'authorized_at', 'captured_at', 'cancelled_at', 'checkout_identity_hash',
        ];

        foreach (array_keys($extra) as $column) {
            if (!in_array($column, $allowedExtra, true)) {
                throw new RuntimeException('Stripe attempt column not allowed.');
            }
        }

        return DB::table('stripe_payment_attempts')
            ->where('id', $attemptId)
            ->where('status', $from)
            ->update(array_merge($extra, [
                'status' => $to,
                'updated_at' => now(),
            ])) === 1;
    }

    public function isExpired(object $attempt): bool
    {
        // Rows without an expiry (created before Fix 8) are treated as expired.
        if (empty($attempt->expires_at)) {
            return true;
        }

        return now()->greaterThanOrEqualTo(\Illuminate\Support\Carbon::parse($attempt->expires_at));
    }
}
