<?php

namespace App\Services;

use RuntimeException;

class StripeTestAttemptService
{
    public function generateIdentifiers(): array
    {
        $reference = bin2hex(random_bytes(16));

        return [
            'reference' => $reference,
            'reference_hash' => hash('sha256', $reference),
            'idempotency_key' => 'fh_stripe_test_' . $reference,
        ];
    }

    public function validateIdentifiers(array $data): bool
    {
        $reference = $data['reference'] ?? null;
        $hash = $data['reference_hash'] ?? null;
        $key = $data['idempotency_key'] ?? null;

        if (
            !is_string($reference) ||
            !preg_match('/^[a-f0-9]{32}$/D', $reference) ||
            !is_string($hash) ||
            !is_string($key)
        ) {
            throw new RuntimeException('Invalid payment identifiers.');
        }

        if (
            !hash_equals(hash('sha256', $reference), $hash) ||
            !hash_equals('fh_stripe_test_' . $reference, $key)
        ) {
            throw new RuntimeException('Payment identifier mismatch.');
        }

        return true;
    }
}
