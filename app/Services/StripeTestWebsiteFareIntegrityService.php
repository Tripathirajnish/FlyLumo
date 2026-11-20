<?php

namespace App\Services;

use RuntimeException;

class StripeTestWebsiteFareIntegrityService
{
    public function validate(array $priceInfo, array $fields): int
    {
        $precision = app(StripeTestPricePrecisionService::class);
        $totalCents = 0;

        foreach ([
            'Adult' => 'Adults',
            'Child' => 'Childrens',
            'Infant' => 'Infants',
        ] as $category => $field) {
            $count = $fields[$field] ?? null;
            $row = $priceInfo[$category] ?? null;

            if (
                !is_array($row) ||
                !(
                    (is_int($count) && $count >= 0 && $count <= 9) ||
                    (is_string($count) && preg_match('/^[0-9]$/D', $count) === 1)
                )
            ) {
                throw new RuntimeException('Invalid Stripe website passenger breakdown.');
            }

            $rowCount = $row['Number'] ?? null;

            if (
                !(
                    (is_int($rowCount) && $rowCount >= 0 && $rowCount <= 9) ||
                    (is_string($rowCount) && preg_match('/^[0-9]$/D', $rowCount) === 1)
                ) ||
                (int) $rowCount !== (int) $count
            ) {
                throw new RuntimeException('Stripe website passenger count mismatch.');
            }

            $price = $row['Price'] ?? null;
            $subtotal = $row['SubTotal'] ?? null;

            if ((int) $count === 0 && ($price === null || $price === '')) {
                $price = 0;
            }

            if ((int) $count > 0 && ($price === null || $price === '')) {
                throw new RuntimeException('Required Stripe passenger fare is missing.');
            }

            $priceCents = $precision->toCents($price);

            if ((int) $count === 0 && ($subtotal === null || $subtotal === '')) {
                $subtotal = 0;
            }

            $subtotalCents = $precision->toCents($subtotal);

            if ($subtotalCents !== $priceCents * (int) $count) {
                throw new RuntimeException('Stripe website passenger subtotal mismatch.');
            }

            $totalCents += $subtotalCents;
        }

        $declaredTotal = $precision->validateTotal($priceInfo['Total'] ?? null);

        if ($totalCents !== $declaredTotal) {
            throw new RuntimeException('Stripe website total fare mismatch.');
        }

        return $totalCents;
    }
}
