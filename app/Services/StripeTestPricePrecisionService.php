<?php

namespace App\Services;

use RuntimeException;

class StripeTestPricePrecisionService
{
    public function toCents($value): int
    {
        if (!is_string($value) && !is_int($value) && !is_float($value)) {
            throw new RuntimeException('Invalid Stripe price type.');
        }

        $amount = (string) $value;

        if (!preg_match('/^(?:0|[1-9][0-9]{0,6})(?:\.[0-9]{1,2})?$/D', $amount)) {
            throw new RuntimeException('Invalid Stripe USD price precision.');
        }

        $parts = explode('.', $amount, 2);

        $cents = ((int) $parts[0] * 100)
            + (int) str_pad($parts[1] ?? '', 2, '0');

        if ($cents > 99999999) {
            throw new RuntimeException('Stripe price exceeds permitted range.');
        }

        return $cents;
    }

    public function validateTotal($value): int
    {
        $cents = $this->toCents($value);

        if ($cents < 50) {
            throw new RuntimeException('Stripe payment amount is below minimum.');
        }

        return $cents;
    }
}
