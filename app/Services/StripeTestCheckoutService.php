<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Stripe-only (Step D): turn a verified fare + fresh supplier quote into a
 * Stripe TEST Checkout Session URL.
 *
 *  - One attempt per checkout identity (session + flow + search + offer +
 *    amount + passengers), created/reused atomically (Fix 8).
 *  - A pending attempt with an OPEN Stripe session is reused (same URL);
 *    an expired Stripe session closes the attempt and a new one is made.
 *  - Stripe create uses the attempt idempotency key, so a retry/double
 *    click can never create two Stripe sessions for one attempt.
 *  - Manual capture only. Nothing is marked paid here; only the webhook
 *    (Step E) may move an attempt to authorized.
 */
class StripeTestCheckoutService
{
    /**
     * Stripe session lifetime, counted from the ATTEMPT's created_at so that a
     * retry sends byte-identical parameters with the same idempotency key.
     * The attempt is only startable for 30 min (Fix 8 TTL), so this is always
     * 30-60 min ahead - inside Stripe's allowed 30 min - 24 h window.
     */
    public const STRIPE_SESSION_MINUTES = 60;

    public function start(string $sessionId, string $code, array $fare, array $quote): array
    {
        $flow = $fare['flow'] ?? null;

        if (!in_array($flow, ['website', 'fareleaders'], true)) {
            throw new RuntimeException('Unsupported Stripe checkout flow.');
        }

        $offer = \Illuminate\Support\Facades\Session::get(
            $code.($flow === 'fareleaders' ? '.FL-OfferCode' : '.Selected-OfferCode')
        );

        if (!is_string($offer) || $offer === '') {
            throw new RuntimeException('Stripe selected offer is missing.');
        }

        $fields = app(\App\Http\Controllers\FlightController::class)->getSearchField($code);
        $passengers = (int) ($fields['Adults'] ?? 0) + (int) ($fields['Childrens'] ?? 0) + (int) ($fields['Infants'] ?? 0);

        $identity = app(StripeTestCheckoutIdentityService::class)->generate(
            $sessionId,
            $flow,
            $code,
            $offer,
            (int) $fare['amount_cents'],
            $passengers
        );

        $context = [
            'supplier_amount_cents' => (int) $quote['supplier_amount_cents'],
            'book_limit' => $quote['book_limit'] ?? null,
            'search_code' => $code,
        ];

        // At most two rounds: the second only after closing an expired Stripe session.
        for ($round = 0; $round < 2; $round++) {
            $attempt = app(StripeTestAtomicAttemptService::class)
                ->createOrReuse($flow, (int) $fare['amount_cents'], $identity, $context);

            if ($attempt->status === 'pending') {
                $url = $this->reuseOpenSession($attempt);

                if ($url !== null) {
                    return ['url' => $url, 'attempt_id' => (int) $attempt->id, 'reused' => true];
                }

                continue; // attempt was closed; create a fresh one
            }

            return $this->createSession($attempt);
        }

        throw new RuntimeException('Stripe checkout could not be started, please retry.');
    }

    private function reuseOpenSession(object $attempt): ?string
    {
        if (empty($attempt->stripe_checkout_session_id)) {
            throw new RuntimeException('Pending Stripe attempt has no session.');
        }

        $session = $this->stripe()->checkout->sessions->retrieve($attempt->stripe_checkout_session_id);

        if (($session->status ?? null) === 'open' && is_string($session->url ?? null)) {
            return $session->url;
        }

        if (($session->status ?? null) === 'complete') {
            // Customer already paid on this session: never start a second payment.
            throw new RuntimeException('This payment is already being processed.');
        }

        // Expired on Stripe: close the attempt and free its identity.
        app(StripeTestAttemptStateService::class)->transition(
            (int) $attempt->id,
            'pending',
            'expired',
            ['checkout_identity_hash' => null]
        );

        return null;
    }

    private function createSession(object $attempt): array
    {
        if ($attempt->status !== 'created') {
            throw new RuntimeException('Stripe attempt is not in a startable state.');
        }

        $reference = (string) $attempt->reference_hash;

        $payload = app(StripeTestCheckoutSessionService::class)
            ->buildPayload((int) $attempt->amount_cents, $reference);

        $payload = array_merge($payload, app(StripeTestReturnUrlService::class)->build($reference), [
            'client_reference_id' => $reference,
            'expires_at' => \Illuminate\Support\Carbon::parse($attempt->created_at)
                ->addMinutes(self::STRIPE_SESSION_MINUTES)->getTimestamp(),
            'metadata' => [
                'attempt_id' => (string) $attempt->id,
                'reference' => $reference,
                'environment' => app(StripeTestPaymentService::class)->mode() === 'live' ? 'live' : 'test',
            ],
        ]);

        $payload['payment_intent_data']['metadata']['attempt_id'] = (string) $attempt->id;

        $session = $this->stripe()->checkout->sessions->create(
            $payload,
            ['idempotency_key' => (string) $attempt->idempotency_key]
        );

        if (!is_string($session->id ?? null) || !is_string($session->url ?? null)) {
            throw new RuntimeException('Stripe did not return a checkout session.');
        }

        $moved = app(StripeTestAttemptStateService::class)->transition(
            (int) $attempt->id,
            'created',
            'pending',
            ['stripe_checkout_session_id' => $session->id]
        );

        if (!$moved) {
            // A parallel request moved it first. Thanks to the idempotency key it
            // received the SAME Stripe session, so the URL is still correct.
            $current = DB::table('stripe_payment_attempts')->where('id', $attempt->id)->first();

            if (!$current || $current->stripe_checkout_session_id !== $session->id) {
                throw new RuntimeException('Stripe attempt changed, please retry.');
            }
        }

        return ['url' => $session->url, 'attempt_id' => (int) $attempt->id, 'reused' => false];
    }

    /**
     * Stripe client (test key only - see StripeTestPaymentService::client()).
     * Overridable in tests so no real API call is made.
     */
    protected function stripe(): object
    {
        return app(StripeTestPaymentService::class)->client();
    }
}
