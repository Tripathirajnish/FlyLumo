<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use RuntimeException;

class StripeTestSupplierFareService
{
    public function validate(array $response, array $fields): array
    {
        $precision = app(StripeTestPricePrecisionService::class);

        if ((string) ($response['Success'] ?? '') !== '1') {
            throw new RuntimeException('Supplier prebook was not successful.');
        }

        // Real AeroPrebook response (verified 3 Nov 2026) carries the currency
        // at top level ("Currency"); PaymentRealPrices is not returned.
        $currency = $response['Currency'] ?? null;

        if (!is_string($currency) || $currency !== 'USD') {
            throw new RuntimeException('Supplier USD currency is not confirmed.');
        }

        // If the supplier ever also returns PaymentRealPrices, it must agree.
        if (
            isset($response['PaymentRealPrices']) &&
            ($response['PaymentRealPrices']['CurrencyCode'] ?? null) !== 'USD'
        ) {
            throw new RuntimeException('Supplier payment currency mismatch.');
        }

        $tariff = $response['TariffInfo'] ?? null;

        if (!is_array($tariff)) {
            throw new RuntimeException('Supplier tariff breakdown is missing.');
        }

        $counts = [];
        $totalCents = 0;

        foreach ([
            'AdultPrice' => 'Adults',
            'ChildPrice' => 'Childrens',
            'InfantPrice' => 'Infants',
        ] as $priceKey => $countKey) {
            $count = $fields[$countKey] ?? null;

            if (
                !(is_int($count) && $count >= 0 && $count <= 9) &&
                !(is_string($count) && preg_match('/^[0-9]$/D', $count) === 1)
            ) {
                throw new RuntimeException('Invalid supplier passenger count.');
            }

            $count = (int) $count;
            $counts[$countKey] = $count;

            $price = $tariff[$priceKey] ?? null;

            if ($count === 0 && ($price === null || $price === '')) {
                $price = 0;
            }

            if ($count > 0 && ($price === null || $price === '')) {
                throw new RuntimeException('Required supplier passenger fare is missing.');
            }

            $totalCents += $precision->toCents($price) * $count;
        }

        if (
            $counts['Adults'] < 1 ||
            $counts['Infants'] > $counts['Adults'] ||
            array_sum($counts) > 9
        ) {
            throw new RuntimeException('Invalid supplier passenger composition.');
        }

        $supplierTotalCents = $precision->validateTotal(
            $response['FullPrice'] ?? null
        );

        if ($totalCents !== $supplierTotalCents) {
            // L1 fix (16 Nov 2026), owner rule "booking nahi rukni chahiye":
            // a per-passenger breakdown that does not add up to FullPrice no longer
            // blocks the payment. FullPrice is the amount the supplier charges and
            // is still checked against the searched fare in
            // StripeTestSupplierQuoteService::compare() (fare changed -> 409).
            // The numbers are logged so the real cause can be seen.
            Log::warning('FARESHUNT_STRIPE_SUPPLIER_BREAKDOWN_MISMATCH', [
                'breakdown_cents' => $totalCents,
                'full_price_cents' => $supplierTotalCents,
                'difference_cents' => $supplierTotalCents - $totalCents,
                'counts' => $counts,
                'adult_price' => is_scalar($tariff['AdultPrice'] ?? null) ? (string) $tariff['AdultPrice'] : null,
                'child_price' => is_scalar($tariff['ChildPrice'] ?? null) ? (string) $tariff['ChildPrice'] : null,
                'infant_price' => is_scalar($tariff['InfantPrice'] ?? null) ? (string) $tariff['InfantPrice'] : null,
                'tariff_keys' => array_slice(array_map('strval', array_keys($tariff)), 0, 30),
            ]);
        }

        return [
            'currency' => 'usd',
            'supplier_amount_cents' => $supplierTotalCents,
        ];
    }
}
