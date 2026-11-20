<?php

namespace App\Services;

use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Stripe-only (Step E): the ONLY place that may mark an attempt authorized.
 *
 *  1. Signature verified with the dedicated whsec_ secret (5 min tolerance).
 *  2. Events from the other mode (test vs live) are rejected.
 *  3. Every event id is stored once (stripe_webhook_events); a processed
 *     event is never processed twice (Stripe retries / replays are safe).
 *  4. The attempt row is locked; amount, currency, manual capture and
 *     reference must all match before pending -> authorized.
 *  5. A hold that arrives for an attempt we can no longer honour
 *     (expired / cancelled / mismatch) is CANCELLED so money is released.
 *
 * Order of events does not matter: authorization is driven by the
 * PaymentIntent metadata (attempt_id + reference) alone.
 */
class StripeTestWebhookService
{
    public const TOLERANCE_SECONDS = 300;

    /** @return array{0:int,1:string} HTTP status + short result */
    public function handle(string $payload, ?string $signature): array
    {
        $secret = app(StripeTestPaymentService::class)->webhookSecret();

        if (!is_string($secret) || !str_starts_with($secret, 'whsec_')) {
            return [503, 'webhook not configured'];
        }

        try {
            $event = \Stripe\Webhook::constructEvent($payload, (string) $signature, $secret, self::TOLERANCE_SECONDS);
        } catch (\Throwable $e) {
            Log::warning('FARESHUNT_STRIPE_TEST_WEBHOOK_BAD_SIGNATURE', ['error' => get_class($e)]);
            return [400, 'invalid signature'];
        }

        $data = $event->toArray();

        $expectLive = app(StripeTestPaymentService::class)->isLive();
        if (($data['livemode'] ?? null) !== $expectLive) {
            Log::warning('FARESHUNT_STRIPE_WEBHOOK_MODE_MISMATCH', ['event' => $data['id'] ?? null, 'livemode' => $data['livemode'] ?? null, 'expect_live' => $expectLive]);
            return [400, 'wrong mode'];
        }

        $eventId = (string) ($data['id'] ?? '');
        $type = (string) ($data['type'] ?? '');
        $object = $data['data']['object'] ?? [];

        if (!preg_match('/^evt_[A-Za-z0-9]+$/D', $eventId) || $type === '' || !is_array($object)) {
            return [400, 'malformed event'];
        }

        if (!$this->claimEvent($eventId, $type, $object)) {
            return [200, 'duplicate'];
        }

        try {
            $result = DB::transaction(fn () => $this->process($type, $object), 3);

            DB::table('stripe_webhook_events')->where('stripe_event_id', $eventId)->update([
                'processing_status' => 'processed',
                'last_error_code' => null,
                'processed_at' => now(),
                'updated_at' => now(),
            ]);

            return [200, $result];
        } catch (\Throwable $e) {
            DB::table('stripe_webhook_events')->where('stripe_event_id', $eventId)->update([
                'processing_status' => 'failed',
                'last_error_code' => mb_substr(class_basename($e), 0, 100),
                'updated_at' => now(),
            ]);

            Log::error('FARESHUNT_STRIPE_TEST_WEBHOOK_FAILED', ['event' => $eventId, 'type' => $type, 'error' => get_class($e)]);

            return [500, 'processing failed']; // Stripe will retry
        }
    }

    /**
     * true = this request owns processing; false = already processed.
     */
    private function claimEvent(string $eventId, string $type, array $object): bool
    {
        $pi = $this->paymentIntentId($type, $object);

        try {
            DB::table('stripe_webhook_events')->insert([
                'stripe_event_id' => $eventId,
                'event_type' => mb_substr($type, 0, 150),
                'stripe_payment_intent_id' => $pi,
                'processing_status' => 'received',
                'attempt_count' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return true;
        } catch (UniqueConstraintViolationException $e) {
            // Seen before: only re-process if the earlier run did not finish.
            $updated = DB::table('stripe_webhook_events')
                ->where('stripe_event_id', $eventId)
                ->whereIn('processing_status', ['received', 'failed'])
                ->update([
                    'attempt_count' => DB::raw('attempt_count + 1'),
                    'processing_status' => 'received',
                    'updated_at' => now(),
                ]);

            return $updated === 1;
        }
    }

    private function process(string $type, array $o): string
    {
        switch ($type) {
            case 'payment_intent.amount_capturable_updated':
                return $this->onAuthorized($o);
            case 'checkout.session.completed':
                return $this->onSessionCompleted($o);
            case 'checkout.session.expired':
                return $this->onSessionExpired($o);
            case 'payment_intent.canceled':
                return $this->onIntentCanceled($o);
            // Step S1 (6 Nov 2026): money actions done outside the CRM (e.g. Stripe dashboard).
            case 'payment_intent.succeeded':
                return $this->onCaptured($o);
            case 'charge.refunded':
                return $this->onChargeRefunded($o);
            case 'charge.refund.updated':
                return $this->onRefundUpdated($o);
            case 'payment_intent.payment_failed':
                return 'payment failed recorded'; // customer may retry on the same session
            default:
                return 'ignored';
        }
    }

    private function onAuthorized(array $pi): string
    {
        $piId = (string) ($pi['id'] ?? '');
        $attempt = $this->lockAttempt($pi['metadata'] ?? []);

        if (!$attempt) {
            $this->cancelIntent($piId, 'unknown_attempt');
            return 'unknown attempt - hold cancelled';
        }

        if ($attempt->status === 'authorized' && $attempt->stripe_payment_intent_id === $piId) {
            return 'already authorized';
        }

        $ok = $attempt->status === 'pending'
            && preg_match('/^pi_[A-Za-z0-9]+$/D', $piId)
            && ($pi['status'] ?? '') === 'requires_capture'
            && ($pi['capture_method'] ?? '') === 'manual'
            && strtolower((string) ($pi['currency'] ?? '')) === 'usd'
            && (int) ($pi['amount_capturable'] ?? -1) === (int) $attempt->amount_cents
            && (int) ($pi['amount'] ?? -1) === (int) $attempt->amount_cents;

        if (!$ok) {
            $this->cancelIntent($piId, 'mismatch_or_closed');

            if (in_array($attempt->status, ['created', 'pending'], true)) {
                app(StripeTestAttemptStateService::class)->transition(
                    (int) $attempt->id, (string) $attempt->status, 'failed',
                    ['last_error_code' => 'webhook_mismatch', 'checkout_identity_hash' => null]
                );
            }

            return 'rejected - hold cancelled';
        }

        app(StripeTestAttemptStateService::class)->transition(
            (int) $attempt->id, 'pending', 'authorized',
            ['stripe_payment_intent_id' => $piId, 'authorized_at' => now()]
        );

        return 'authorized';
    }

    private function onSessionCompleted(array $s): string
    {
        $attempt = $this->lockAttempt($s['metadata'] ?? []);

        if (!$attempt || $attempt->stripe_checkout_session_id !== ($s['id'] ?? null)) {
            return 'session not matched';
        }

        $piId = $s['payment_intent'] ?? null;

        if (is_string($piId) && preg_match('/^pi_[A-Za-z0-9]+$/D', $piId) && empty($attempt->stripe_payment_intent_id)) {
            DB::table('stripe_payment_attempts')->where('id', $attempt->id)
                ->update(['stripe_payment_intent_id' => $piId, 'updated_at' => now()]);
        }

        return 'session linked';
    }

    private function onSessionExpired(array $s): string
    {
        $attempt = $this->lockAttempt($s['metadata'] ?? []);

        if ($attempt && $attempt->status === 'pending' && $attempt->stripe_checkout_session_id === ($s['id'] ?? null)) {
            app(StripeTestAttemptStateService::class)->transition(
                (int) $attempt->id, 'pending', 'expired', ['checkout_identity_hash' => null]
            );
            return 'attempt expired';
        }

        return 'no change';
    }

    private function onIntentCanceled(array $pi): string
    {
        $attempt = $this->lockAttempt($pi['metadata'] ?? []);

        if ($attempt && in_array($attempt->status, ['pending', 'authorized'], true)) {
            app(StripeTestAttemptStateService::class)->transition(
                (int) $attempt->id, (string) $attempt->status, 'cancelled',
                ['cancelled_at' => now(), 'checkout_identity_hash' => null]
            );
            return 'attempt cancelled';
        }

        return 'no change';
    }

    /**
     * Step S1: the payment was captured (CRM or Stripe dashboard). authorized -> captured.
     * last_error_code is left alone (it may hold a manual-review reason) unless the
     * captured amount differs from the held amount.
     */
    private function onCaptured(array $pi): string
    {
        $piId = (string) ($pi['id'] ?? '');
        $attempt = $this->lockAttempt($pi['metadata'] ?? []);

        if (!$attempt || $attempt->stripe_payment_intent_id !== $piId) {
            return 'capture not matched';
        }
        if (in_array($attempt->status, ['captured', 'refunded'], true)) {
            return 'already captured';
        }
        if ($attempt->status !== 'authorized' || ($pi['status'] ?? '') !== 'succeeded') {
            return 'no change';
        }

        $received = (int) ($pi['amount_received'] ?? 0);
        $extra = ['captured_at' => now()];
        if ($received !== (int) $attempt->amount_cents) {
            $extra['last_error_code'] = 'partial_capture_'.$received;
        }

        app(StripeTestAttemptStateService::class)->transition((int) $attempt->id, 'authorized', 'captured', $extra);
        Log::info('FARESHUNT_STRIPE_CAPTURE_SYNCED', ['attempt_id' => (int) $attempt->id, 'amount_received' => $received]);

        return 'capture recorded';
    }

    /** Step S1: refund from CRM or Stripe dashboard. Full -> refunded; partial -> noted, status kept. */
    private function onChargeRefunded(array $ch): string
    {
        $attempt = $this->lockByIntent($ch['payment_intent'] ?? null);

        if (!$attempt) {
            return 'refund not matched';
        }
        if ($attempt->status === 'refunded') {
            return 'already refunded';
        }
        if ($attempt->status !== 'captured') {
            return 'no change';
        }

        $captured = (int) ($ch['amount_captured'] ?? ($ch['amount'] ?? 0));
        $refunded = (int) ($ch['amount_refunded'] ?? 0);

        if (!empty($ch['refunded']) && $captured > 0 && $refunded >= $captured) {
            app(StripeTestAttemptStateService::class)->transition((int) $attempt->id, 'captured', 'refunded');
            Log::info('FARESHUNT_STRIPE_REFUND_SYNCED', ['attempt_id' => (int) $attempt->id, 'amount_refunded' => $refunded]);

            return 'refund recorded';
        }

        DB::table('stripe_payment_attempts')->where('id', $attempt->id)
            ->update(['last_error_code' => 'partial_refund_'.$refunded, 'updated_at' => now()]);
        Log::warning('FARESHUNT_STRIPE_PARTIAL_REFUND', ['attempt_id' => (int) $attempt->id, 'amount_refunded' => $refunded, 'amount_captured' => $captured]);

        return 'partial refund recorded';
    }

    /** Step S1: a refund that Stripe later reports as failed/canceled is flagged for the team. */
    private function onRefundUpdated(array $rf): string
    {
        if (!in_array($rf['status'] ?? '', ['failed', 'canceled'], true)) {
            return 'no change';
        }

        $attempt = $this->lockByIntent($rf['payment_intent'] ?? null);

        if (!$attempt) {
            return 'refund not matched';
        }

        DB::table('stripe_payment_attempts')->where('id', $attempt->id)
            ->update(['last_error_code' => 'refund_'.$rf['status'], 'updated_at' => now()]);
        Log::error('FARESHUNT_STRIPE_REFUND_FAILED', ['attempt_id' => (int) $attempt->id, 'refund_status' => $rf['status']]);

        return 'refund failure recorded';
    }

    /** Step S1: charges/refunds carry the PaymentIntent id, not our metadata. */
    private function lockByIntent($piId): ?object
    {
        if (!is_string($piId) || !preg_match('/^pi_[A-Za-z0-9]+$/D', $piId)) {
            return null;
        }

        return DB::table('stripe_payment_attempts')
            ->where('stripe_payment_intent_id', $piId)
            ->lockForUpdate()
            ->first();
    }

    /** Attempt must match BOTH attempt_id and reference from our own metadata. */
    private function lockAttempt(array $metadata): ?object
    {
        $id = $metadata['attempt_id'] ?? null;
        $ref = $metadata['reference'] ?? null;

        if (!is_string($id) || !ctype_digit($id) || !is_string($ref) || !preg_match('/^[a-f0-9]{64}$/D', $ref)) {
            return null;
        }

        return DB::table('stripe_payment_attempts')
            ->where('id', (int) $id)
            ->where('reference_hash', $ref)
            ->lockForUpdate()
            ->first();
    }

    private function cancelIntent(string $piId, string $reason): void
    {
        if (!preg_match('/^pi_[A-Za-z0-9]+$/D', $piId)) {
            return;
        }

        Log::warning('FARESHUNT_STRIPE_TEST_HOLD_CANCELLED', ['payment_intent' => $piId, 'reason' => $reason]);

        $this->stripe()->paymentIntents->cancel($piId, ['cancellation_reason' => 'abandoned']);
    }

    private function paymentIntentId(string $type, array $o): ?string
    {
        $id = str_starts_with($type, 'payment_intent.') ? ($o['id'] ?? null) : ($o['payment_intent'] ?? null);

        return is_string($id) && preg_match('/^pi_[A-Za-z0-9]+$/D', $id) ? $id : null;
    }

    /** Test-key-only client; overridable in tests. */
    protected function stripe(): object
    {
        return app(StripeTestPaymentService::class)->client();
    }
}
