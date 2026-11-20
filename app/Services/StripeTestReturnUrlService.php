<?php

namespace App\Services;

use RuntimeException;

class StripeTestReturnUrlService
{
    public function build(string $reference): array
    {
        if (!preg_match('/^[a-zA-Z0-9_-]{1,128}$/D', $reference)) {
            throw new RuntimeException('Invalid checkout reference.');
        }

        $base = config('app.url');

        if (!is_string($base)) {
            throw new RuntimeException('Invalid application URL.');
        }

        $parts = parse_url($base);

        if (
            $parts === false ||
            ($parts['scheme'] ?? '') !== 'https' ||
            ($parts['host'] ?? '') !== 'fareshunt.com' ||
            isset($parts['user']) ||
            isset($parts['pass']) ||
            isset($parts['query']) ||
            isset($parts['fragment'])
        ) {
            throw new RuntimeException('Unsafe checkout return URL.');
        }

        $origin = 'https://fareshunt.com';

        return [
            'success_url' => $origin
                . '/stripe-test/return/success/'
                . rawurlencode($reference)
                . '?session_id={CHECKOUT_SESSION_ID}',

            'cancel_url' => $origin
                . '/stripe-test/return/cancel/'
                . rawurlencode($reference),
        ];
    }
}
