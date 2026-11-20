<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use RuntimeException;

class StripeTestAttemptRepository
{
    public function findReusable(
        string $referenceHash,
        string $flow,
        int $amountCents
    ): ?object {
        if (config('stripe_checkout.enabled') !== true) {
            throw new RuntimeException('Stripe checkout is disabled.');
        }

        if (!preg_match('/^[a-f0-9]{64}$/D', $referenceHash)) {
            throw new RuntimeException('Invalid reference hash.');
        }

        if (!in_array($flow, ['website', 'fareleaders'], true)) {
            throw new RuntimeException('Invalid payment flow.');
        }

        if ($amountCents < 50 || $amountCents > 99999999) {
            throw new RuntimeException('Invalid payment amount.');
        }

        $attempt = DB::table('stripe_payment_attempts')
            ->where('reference_hash', $referenceHash)
            ->first();

        if (!$attempt) {
            return null;
        }

        if (
            $attempt->flow !== $flow ||
            (int) $attempt->amount_cents !== $amountCents ||
            $attempt->currency !== 'USD'
        ) {
            throw new RuntimeException('Payment attempt mismatch.');
        }

        if (!in_array($attempt->status, ['created', 'pending'], true)) {
            throw new RuntimeException('Payment attempt cannot be reused.');
        }

        // Fix 8: expired attempts are never reused.
        if (app(StripeTestAttemptStateService::class)->isExpired($attempt)) {
            throw new RuntimeException('Payment attempt has expired.');
        }

        return $attempt;
    }

    public function create(
        string $flow,
        int $amountCents,
        array $identifiers,
        string $checkoutIdentityHash,
        array $context = []
    ): int {
        if (config('stripe_checkout.enabled') !== true) {
            throw new RuntimeException('Stripe checkout is disabled.');
        }

        if (!in_array($flow, ['website', 'fareleaders'], true)) {
            throw new RuntimeException('Invalid payment flow.');
        }

        if ($amountCents < 50 || $amountCents > 99999999) {
            throw new RuntimeException('Invalid payment amount.');
        }

        app(StripeTestAttemptService::class)
            ->validateIdentifiers($identifiers);

        if (!preg_match('/^[a-f0-9]{64}$/D', $checkoutIdentityHash)) {
            throw new RuntimeException('Invalid checkout identity.');
        }

        // Fix 8: expiry + fresh supplier quote stored on the attempt.
        $quote = $this->validatedContext($context);

        return DB::table('stripe_payment_attempts')->insertGetId([
            'booking_id' => null,
            'flow' => $flow,
            'checkout_identity_hash' => $checkoutIdentityHash,
            'reference_hash' => $identifiers['reference_hash'],
            'idempotency_key' => $identifiers['idempotency_key'],
            'amount_cents' => $amountCents,
            'currency' => 'USD',
            'status' => 'created',
            'booking_status' => 'not_started',
            'expires_at' => now()->addMinutes(self::TTL_MINUTES),
            'supplier_amount_cents' => $quote['supplier_amount_cents'],
            'quote_book_limit' => $quote['book_limit'],
            'search_code_hash' => $quote['search_code_hash'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Attempt lifetime. BookLimit is stored but not yet used for expiry:
     * its real format/timezone is still unverified (open item).
     */
    public const TTL_MINUTES = 30;

    public function validatedContext(array $context): array
    {
        $supplier = $context['supplier_amount_cents'] ?? null;
        $bookLimit = $context['book_limit'] ?? null;
        $searchCode = $context['search_code'] ?? null;

        if ($supplier !== null && (!is_int($supplier) || $supplier < 50 || $supplier > 99999999)) {
            throw new RuntimeException('Invalid supplier quote amount.');
        }

        if ($bookLimit !== null && (!is_string($bookLimit) || mb_strlen($bookLimit) > 40)) {
            throw new RuntimeException('Invalid supplier quote time limit.');
        }

        if ($searchCode !== null && (!is_string($searchCode) || !preg_match('/^[a-zA-Z0-9_-]{1,128}$/D', $searchCode))) {
            throw new RuntimeException('Invalid search reference.');
        }

        return [
            'supplier_amount_cents' => $supplier,
            'book_limit' => $bookLimit,
            'search_code_hash' => $searchCode === null ? null : hash('sha256', $searchCode),
        ];
    }
}
