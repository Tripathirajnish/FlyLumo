<?php

namespace App\Services;

use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class StripeTestAtomicAttemptService
{
    public function createOrReuse(
        string $flow,
        int $amountCents,
        string $checkoutIdentityHash,
        array $context = []
    ): object {
        if (config('stripe_checkout.enabled') !== true) {
            throw new RuntimeException('Stripe checkout is disabled.');
        }

        if (!in_array($flow, ['website', 'fareleaders'], true)) {
            throw new RuntimeException('Invalid payment flow.');
        }

        if ($amountCents < 50 || $amountCents > 99999999) {
            throw new RuntimeException('Invalid payment amount.');
        }

        if (!preg_match('/^[a-f0-9]{64}$/D', $checkoutIdentityHash)) {
            throw new RuntimeException('Invalid checkout identity.');
        }

        // Fix 8: validate the quote context before any DB work.
        app(StripeTestAttemptRepository::class)->validatedContext($context);

        try {
            return DB::transaction(function () use (
                $flow,
                $amountCents,
                $checkoutIdentityHash,
                $context
            ) {
                $attempt = DB::table('stripe_payment_attempts')
                    ->where('checkout_identity_hash', $checkoutIdentityHash)
                    ->lockForUpdate()
                    ->first();

                if ($attempt) {
                    // Fix 8: an expired attempt, or one priced on a different
                    // supplier quote, is closed and a new attempt is created.
                    // Its identity hash is cleared so the unique key is free.
                    if ($this->isStale($attempt, $context)) {
                        $moved = app(StripeTestAttemptStateService::class)->transition(
                            (int) $attempt->id,
                            (string) $attempt->status,
                            'expired',
                            ['checkout_identity_hash' => null]
                        );

                        if (!$moved) {
                            throw new RuntimeException('Payment attempt changed, please retry.');
                        }
                    } else {
                        return $this->validateExisting(
                            $attempt,
                            $flow,
                            $amountCents
                        );
                    }
                }

                $identifiers = app(StripeTestAttemptService::class)
                    ->generateIdentifiers();

                $id = app(StripeTestAttemptRepository::class)->create(
                    $flow,
                    $amountCents,
                    $identifiers,
                    $checkoutIdentityHash,
                    $context
                );

                return DB::table('stripe_payment_attempts')
                    ->where('id', $id)
                    ->first();
            }, 3);
        } catch (UniqueConstraintViolationException $e) {
            if ((int) ($e->errorInfo[1] ?? 0) !== 1062) {
                throw $e;
            }

            $existing = DB::table('stripe_payment_attempts')
                ->where('checkout_identity_hash', $checkoutIdentityHash)
                ->first();

            if (!$existing || $this->isStale($existing, $context)) {
                throw $e;
            }

            return $this->validateExisting(
                $existing,
                $flow,
                $amountCents
            );
        }
    }

    private function isStale(object $attempt, array $context): bool
    {
        if (!in_array($attempt->status, StripeTestAttemptStateService::REUSABLE, true)) {
            return false; // final/authorized rows are handled by validateExisting()
        }

        if (app(StripeTestAttemptStateService::class)->isExpired($attempt)) {
            return true;
        }

        $supplier = $context['supplier_amount_cents'] ?? null;

        return $supplier !== null
            && (int) ($attempt->supplier_amount_cents ?? -1) !== $supplier;
    }

    private function validateExisting(
        object $attempt,
        string $flow,
        int $amountCents
    ): object {
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

        return $attempt;
    }
}
