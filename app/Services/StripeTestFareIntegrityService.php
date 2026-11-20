<?php

namespace App\Services;

use RuntimeException;

class StripeTestFareIntegrityService
{
    public function validateFareleaders(array $markup, array $fields): int
    {
        $payment = app(StripeTestPaymentService::class);

        $expectedTotal = 0;
        $expectedPassengers = 0;

        foreach ([
            ['Adult', 'Adults'],
            ['Child', 'Childrens'],
            ['Infant', 'Infants'],
        ] as [$category, $field]) {
            $count = $fields[$field] ?? null;
            $row = $markup[$category] ?? null;

            if (
                !is_array($row) ||
                !is_int($count) && !(
                    is_string($count) &&
                    preg_match('/^[0-9]$/D', $count)
                ) ||
                (int) $count < 0 ||
                (int) $count > 9 ||
                !isset($row['Number']) ||
                !(
                    (is_int($row['Number']) &&
                     $row['Number'] >= 0 &&
                     $row['Number'] <= 9) ||
                    (is_string($row['Number']) &&
                     preg_match('/^[0-9]$/D', $row['Number']) === 1)
                ) ||
                (int) $row['Number'] !== (int) $count
            ) {
                throw new RuntimeException('Invalid passenger price breakdown.');
            }

            $expectedPassengers += (int) $count;

            $unit = $this->toCents($row['Price'] ?? null, $payment);
            $subtotal = $this->toCents($row['SubTotal'] ?? null, $payment);

            if ($unit * (int) $count !== $subtotal) {
                throw new RuntimeException('Passenger subtotal mismatch.');
            }

            $expectedTotal += $subtotal;
        }

        if ($expectedPassengers < 1 || $expectedPassengers > 9) {
            throw new RuntimeException('Invalid passenger total.');
        }

        $total = $this->toCents($markup['Total'] ?? null, $payment);

        if ($expectedTotal !== $total) {
            throw new RuntimeException('FareLeaders total price mismatch.');
        }

        if ($total < 50 || $total > 99999999) {
            throw new RuntimeException('Final USD payment amount is outside permitted limits.');
        }

        return $total;
    }

    private function toCents($value, StripeTestPaymentService $payment): int
    {
        if (!is_string($value) && !is_int($value) && !is_float($value)) {
            throw new RuntimeException('Invalid fare value.');
        }

        $string = (string) $value;

        if (!preg_match('/^(?:0|[1-9][0-9]{0,6})(?:\.[0-9]{1,2})?$/D', $string)) {
            throw new RuntimeException('Invalid USD fare format.');
        }

        $parts = explode('.', $string, 2);
        $dollars = (int) $parts[0];
        $cents = (int) str_pad($parts[1] ?? '', 2, '0');

        $total = ($dollars * 100) + $cents;

        if ($total > 99999999) {
            throw new RuntimeException('USD fare component exceeds permitted limits.');
        }

        return $total;
    }
}
