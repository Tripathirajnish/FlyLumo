<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

/**
 * Step G5: Super-Admin money actions on one Stripe attempt (full amount only).
 *   capture : authorized + booking booked/manual_review -> captured
 *   cancel  : authorized -> cancelled (hold released)
 *   refund  : captured   -> refunded
 * Each Stripe call uses a fixed idempotency key per attempt+action, so a double click
 * can never charge or refund twice. State changes are compare-and-set. Every action
 * is written to crm_change_logs (module stripe_gateway).
 */
class StripeAttemptActionService
{
    public const ACTIONS = ['capture', 'cancel', 'refund'];

    /** Which actions are possible for this attempt row right now. */
    public function allowed(object $a): array
    {
        $out = [];
        $piOk = is_string($a->stripe_payment_intent_id ?? null) && preg_match('/^pi_[A-Za-z0-9]+$/D', $a->stripe_payment_intent_id);
        if (!$piOk) {
            return [];
        }
        if ($a->status === 'authorized' && in_array((string) $a->booking_status, ['booked', 'manual_review'], true)) {
            $out[] = 'capture';
        }
        if ($a->status === 'authorized' && !in_array((string) $a->booking_status, ['booking'], true)) {
            $out[] = 'cancel';
        }
        if ($a->status === 'captured') {
            $out[] = 'refund';
        }

        return $out;
    }

    /** @return string message for the CRM user */
    public function run(int $attemptId, string $action, int $adminId, string $reason): string
    {
        if (!in_array($action, self::ACTIONS, true)) {
            throw new RuntimeException('Unknown payment action.');
        }
        $a = DB::table('stripe_payment_attempts')->where('id', $attemptId)->first();
        if (!$a) {
            throw new RuntimeException('Payment attempt not found.');
        }
        if (!in_array($action, $this->allowed($a), true)) {
            throw new RuntimeException('This action is not possible for payment #'.$attemptId.' (status: '.$a->status.', booking: '.($a->booking_status ?: '-').').');
        }

        $pi = (string) $a->stripe_payment_intent_id;
        $state = app(StripeTestAttemptStateService::class);
        $key = 'fh-'.$action.'-'.$attemptId;

        if ($action === 'capture') {
            $res = $this->stripe()->paymentIntents->capture($pi, [], ['idempotency_key' => $key]);
            if (($res->status ?? null) !== 'succeeded') {
                throw new RuntimeException('Stripe did not confirm the capture (status: '.($res->status ?? '?').').');
            }
            $state->transition($attemptId, 'authorized', 'captured', ['captured_at' => now()]);
            $new = 'captured';
            $msg = 'Payment #'.$attemptId.' captured: the customer has been charged.';
        } elseif ($action === 'cancel') {
            $res = $this->stripe()->paymentIntents->cancel($pi, ['cancellation_reason' => 'requested_by_customer'], ['idempotency_key' => $key]);
            if (($res->status ?? null) !== 'canceled') {
                throw new RuntimeException('Stripe did not confirm the cancellation (status: '.($res->status ?? '?').').');
            }
            $state->transition($attemptId, 'authorized', 'cancelled', ['cancelled_at' => now(), 'checkout_identity_hash' => null]);
            $new = 'cancelled';
            $msg = 'Payment #'.$attemptId.' hold released: no money will be taken.'
                .($a->booking_status === 'booked' ? ' Remember to cancel the supplier booking too.' : '');
        } else {
            $res = $this->stripe()->refunds->create(['payment_intent' => $pi], ['idempotency_key' => $key]);
            if (!in_array($res->status ?? null, ['succeeded', 'pending'], true)) {
                throw new RuntimeException('Stripe did not accept the refund (status: '.($res->status ?? '?').').');
            }
            $state->transition($attemptId, 'captured', 'refunded');
            $new = 'refunded';
            $msg = 'Payment #'.$attemptId.' refunded in full'.(($res->status ?? '') === 'pending' ? ' (Stripe is still processing it)' : '').'.';
        }

        $this->audit($attemptId, $action, (string) $a->status, $new, $adminId, $reason);
        Log::info('FARESHUNT_STRIPE_CRM_ACTION', ['attempt_id' => $attemptId, 'action' => $action, 'admin_id' => $adminId]);

        return $msg;
    }

    private function audit(int $id, string $action, string $old, string $new, int $adminId, string $reason): void
    {
        if (!Schema::hasTable('crm_change_logs')) {
            return;
        }
        DB::table('crm_change_logs')->insert([
            'module' => 'stripe_gateway',
            'record_key' => 'attempt#'.$id,
            'field' => 'payment',
            'action' => $action,
            'old_value' => $old,
            'new_value' => mb_substr($new.' | reason: '.$reason, 0, 500),
            'admin_id' => $adminId ?: null,
            'created_at' => now(),
        ]);
    }

    /** Test-key-only client; overridable in tests. */
    protected function stripe(): object
    {
        return app(StripeTestPaymentService::class)->client();
    }
}
