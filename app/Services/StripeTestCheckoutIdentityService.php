<?php

namespace App\Services;

use RuntimeException;

class StripeTestCheckoutIdentityService
{
    public function generate(
        string $sessionId,
        string $flow,
        string $searchCode,
        string $offerCode,
        int $amountCents,
        int $passengerCount
    ): string {
        if (
            $sessionId === '' ||
            $searchCode === '' ||
            $offerCode === ''
        ) {
            throw new RuntimeException('Missing checkout identity.');
        }

        if (!in_array($flow, ['website', 'fareleaders'], true)) {
            throw new RuntimeException('Invalid checkout flow.');
        }

        if ($amountCents < 50 || $amountCents > 99999999) {
            throw new RuntimeException('Invalid checkout amount.');
        }

        if ($passengerCount < 1 || $passengerCount > 9) {
            throw new RuntimeException('Invalid passenger count.');
        }

        return hash('sha256', json_encode([
            'version' => 1,
            'session' => $sessionId,
            'flow' => $flow,
            'search' => $searchCode,
            'offer' => $offerCode,
            'amount_cents' => $amountCents,
            'currency' => 'USD',
            'passengers' => $passengerCount,
        ], JSON_THROW_ON_ERROR));
    }
}
