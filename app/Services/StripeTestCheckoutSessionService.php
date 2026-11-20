<?php

namespace App\Services;

use RuntimeException;

class StripeTestCheckoutSessionService
{
    public function buildPayload(
        int $amountCents,
        string $reference
    ): array {
        // Step 1c: active mode comes from the resolver (CRM Test/Live switch).
        $mode = app(StripeTestPaymentService::class)->mode();

        if (
            !in_array($mode, ['test', 'live'], true) ||
            config('stripe_checkout.currency') !== 'usd' ||
            config('stripe_checkout.capture_method') !== 'manual'
        ) {
            throw new RuntimeException('Unsafe Stripe checkout configuration.');
        }

        if ($amountCents < 50 || $amountCents > 99999999) {
            throw new RuntimeException('Invalid Stripe checkout amount.');
        }

        if (!preg_match('/^[a-zA-Z0-9_-]{1,128}$/D', $reference)) {
            throw new RuntimeException('Invalid Stripe checkout reference.');
        }

        return [
            'mode' => 'payment',
            'payment_intent_data' => [
                'capture_method' => 'manual',
                'metadata' => [
                    'reference' => $reference,
                    'environment' => $mode === 'live' ? 'live' : 'test',
                ],
            ],
            'line_items' => [
                [
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => [
                            'name' => 'Fares Hunt Flight Reservation',
                        ],
                        'unit_amount' => $amountCents,
                    ],
                    'quantity' => 1,
                ],
            ],
        ];
    }
}
