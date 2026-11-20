<?php

namespace App\Services;

use App\Http\Controllers\FlightController;
use Illuminate\Support\Facades\Session;
use RuntimeException;

class StripeTestFareService
{
    public function calculate(
        string $code,
        string $flow,
        array $choices = []
    ): array {
        if (!in_array($flow, ['website', 'fareleaders'], true)) {
            throw new RuntimeException('Unsupported payment flow.');
        }

        if (!preg_match('/^[a-zA-Z0-9_-]{1,128}$/D', $code)) {
            throw new RuntimeException('Invalid search reference.');
        }

        $controller = app(FlightController::class);

        if (!$controller->isSessionCode($code)) {
            throw new RuntimeException('Search session is missing.');
        }

        $fields = $controller->getSearchField($code);

        // Stripe-only strict passenger validation for both booking flows.
        $validCount = static function ($value): bool {
            return (is_int($value) && $value >= 0 && $value <= 9)
                || (is_string($value) && preg_match('/^[0-9]$/D', $value) === 1);
        };

        $adults = $fields['Adults'] ?? null;
        $children = $fields['Childrens'] ?? null;
        $infants = $fields['Infants'] ?? null;

        if (
            !$validCount($adults) ||
            !$validCount($children) ||
            !$validCount($infants) ||
            (int) $adults < 1 ||
            (int) $adults > 9 ||
            (int) $children > 9 ||
            (int) $infants > (int) $adults ||
            ((int) $adults + (int) $children + (int) $infants) > 9
        ) {
            throw new RuntimeException('Invalid Stripe passenger combination.');
        }

        if ($flow === 'fareleaders') {
            if (strtoupper((string) ($fields['Currency'] ?? '')) !== 'USD') {
                throw new RuntimeException('Stripe test checkout requires USD.');
            }
        }

        // Stripe-only supplier currency safety check.
        try {
            $mode = \Illuminate\Support\Facades\DB::table('settings')
                ->where('name', 'api_type')
                ->value('value');

            $currency = \Illuminate\Support\Facades\DB::table('settings')
                ->where('name', 'api_live_currency')
                ->value('value');

            if (
                $mode !== 'Live' ||
                strtoupper(trim((string) $currency)) !== 'USD'
            ) {
                throw new RuntimeException('Stripe requires an active USD supplier.');
            }
        } catch (\Throwable $e) {
            throw new RuntimeException(
                'Stripe supplier currency verification failed.',
                0,
                $e
            );
        }

        // Raw supplier total the fresh AeroPrebook FullPrice must match.
        $supplierExpectedCents = null;

        if ($flow === 'fareleaders') {
            $markup = Session::get($code.'.FL-priceMarkup');
            $offer = Session::get($code.'.FL-OfferCode');
            $guid = Session::get($code.'.FL-SearchGuid');
            $base = is_array($markup) ? ($markup['Total'] ?? null) : null;

            // Stripe-only: validate FareLeaders passenger fare breakdown.
            if (!is_array($markup)) {
                throw new RuntimeException('FareLeaders price breakdown is missing.');
            }

            app(StripeTestFareIntegrityService::class)
                ->validateFareleaders($markup, $fields);

            // Step FL (5 Nov 2026): raw supplier FullPrice saved when the
            // deeplink page ran its PreBook. Missing (older page / not yet
            // captured) -> null: the fresh quote at checkout becomes the
            // reference that the executor re-checks before AeroBook.
            $flSupplier = Session::get($code.'.FL-SupplierFullPrice');
            $supplierExpectedCents = ($flSupplier === null || $flSupplier === '')
                ? null
                : app(StripeTestPricePrecisionService::class)->validateTotal($flSupplier);
        } else {
            $markup = Session::get($code.'.Min-Markup');
            $offer = Session::get($code.'.Selected-OfferCode');
            $guid = Session::get($code.'.Search-SearchGuid');
            $priceInfo = Session::get($code.'.priceInfo');

            if (!is_array($priceInfo)) {
                throw new RuntimeException('Stripe website price breakdown is missing.');
            }

            $precision = app(StripeTestPricePrecisionService::class);
            $breakdownCents = app(StripeTestWebsiteFareIntegrityService::class)
                ->validate($priceInfo, $fields);

            $selected = Session::get($code.'.Selected-Result');
            $key = Session::get($code.'.Selected-ArrayKey');
            $filtered = Session::get($code.'.Array-Filter') === 'Yes';
            $results = Session::get(
                $code.($filtered ? '.Arrange-FilterResult' : '.Arrange-Result')
            );

            $current = is_array($results) && (is_int($key) || is_string($key))
                ? ($results[$key] ?? null)
                : null;

            if (
                !is_string($offer) ||
                $offer === '' ||
                !is_array($selected) ||
                !is_array($current) ||
                !is_string($selected['OfferCode'] ?? null) ||
                !is_string($current['OfferCode'] ?? null) ||
                !hash_equals($offer, $selected['OfferCode']) ||
                !hash_equals($offer, $current['OfferCode'])
            ) {
                throw new RuntimeException('Stripe selected flight mismatch.');
            }

            $isMarkup = $selected['Is_Markup'] ?? null;

            if (
                !in_array($isMarkup, ['Yes', 'No'], true) ||
                ($current['Is_Markup'] ?? null) !== $isMarkup
            ) {
                throw new RuntimeException('Stripe markup state mismatch.');
            }

            if ($isMarkup === 'Yes') {
                if (
                    !is_array($markup) ||
                    !is_array($selected['MinMarkup'] ?? null) ||
                    !is_array($current['MinMarkup'] ?? null)
                ) {
                    throw new RuntimeException('Stripe website markup is missing.');
                }

                $base = $markup['TotalPrice'] ?? null;
                $expectedSelected = $selected['MinMarkup']['TotalPrice'] ?? null;
                $expectedCurrent = $current['MinMarkup']['TotalPrice'] ?? null;
            } else {
                if (
                    (is_array($markup) && !empty($markup)) ||
                    !empty($selected['MinMarkup']) ||
                    !empty($current['MinMarkup'])
                ) {
                    throw new RuntimeException('Unexpected Stripe website markup.');
                }

                $base = $priceInfo['Total'] ?? null;
                $expectedSelected = $selected['TotalPrice'] ?? null;
                $expectedCurrent = $current['TotalPrice'] ?? null;
            }

            $baseCents = $precision->validateTotal($base);

            if (
                $breakdownCents !== $baseCents ||
                $precision->validateTotal($expectedSelected) !== $baseCents ||
                $precision->validateTotal($expectedCurrent) !== $baseCents ||
                $precision->validateTotal($selected['priceInfo']['Total'] ?? null) !== $baseCents ||
                $precision->validateTotal($current['priceInfo']['Total'] ?? null) !== $baseCents
            ) {
                throw new RuntimeException('Stripe website fare mismatch.');
            }

            // Supplier AeroPrebook FullPrice = raw supplier TotalPrice of the
            // offer (verified on a live response, 3 Nov 2026). With markup the
            // customer pays MinMarkup.TotalPrice, but the supplier still
            // quotes the raw TotalPrice of the selected result.
            if ($isMarkup === 'Yes') {
                $supplierExpectedCents = $precision->validateTotal($selected['TotalPrice'] ?? null);

                if ($precision->validateTotal($current['TotalPrice'] ?? null) !== $supplierExpectedCents) {
                    throw new RuntimeException('Stripe supplier fare mismatch.');
                }
            } else {
                $supplierExpectedCents = $baseCents;
            }

            // Preserve the complete passenger breakdown for filterPrice().
            $markup = $priceInfo;
        }

        if (
            !$offer ||
            !$guid ||
            !is_array($markup) ||
            !is_numeric($base) ||
            !is_finite((float) $base) ||
            (float) $base <= 0
        ) {
            throw new RuntimeException('Trusted flight fare is unavailable.');
        }

        // Stripe-only fail-closed guard for premium add-on pricing.
        $allowedChoiceKeys = ['Web_Check_In', 'Cancellation'];

        try {
            if (!\Illuminate\Support\Facades\Schema::hasTable('booking_addons')) {
                throw new RuntimeException('Premium add-on pricing is unavailable.');
            }

            $activeAddons = \Illuminate\Support\Facades\DB::table('booking_addons')
                ->where('is_active', 1)
                ->get(['key', 'price']);

            foreach ($activeAddons as $addon) {
                if (
                    !is_string($addon->key) ||
                    $addon->key === '' ||
                    !is_numeric($addon->price) ||
                    !is_finite((float) $addon->price) ||
                    (float) $addon->price < 0
                ) {
                    throw new RuntimeException('Invalid premium add-on pricing.');
                }

                // Stripe-only: reject fractional-cent CRM prices.
                app(StripeTestPricePrecisionService::class)
                    ->toCents($addon->price);

                $allowedChoiceKeys[] = $addon->key;
            }
        } catch (\Throwable $e) {
            throw new RuntimeException(
                'Stripe premium add-on pricing verification failed.',
                0,
                $e
            );
        }

        // Stripe-only: pass only whitelisted add-on keys with strict Yes/No.
        $choices = $this->normalizeChoices($choices, $allowedChoiceKeys);

        $pricing = $controller->filterPrice(
            $markup,
            $choices,
            (float) $base,
            $fields
        );

        $total = $pricing['FullP'] ?? null;

        if (!is_numeric($total) || !is_finite((float) $total)) {
            throw new RuntimeException('Invalid calculated fare.');
        }

        // Stripe-only: reject material fractional-cent discrepancies.
        // Tolerate only insignificant PHP floating-point representation noise.
        $rawCents = (float) $total * 100;
        $nearestCents = round($rawCents);

        if (abs($rawCents - $nearestCents) > 0.000001) {
            throw new RuntimeException('Stripe calculated fare has invalid cent precision.');
        }

        $amount = number_format($nearestCents / 100, 2, '.', '');

        $cents = app(StripeTestPricePrecisionService::class)
            ->validateTotal($amount);

        return [
            'flow' => $flow,
            'amount_cents' => $cents,
            'currency' => 'usd',
            'service_pricing' => $pricing['ServiceP'] ?? null,
            'supplier_expected_cents' => $supplierExpectedCents,
        ];
    }

    /**
     * Stripe-only: keep the customer's add-on choices to known keys and
     * strict Yes/No values. Prices are never taken from the browser;
     * filterPrice() recalculates them from Settings / CRM booking_addons.
     * Unknown keys are dropped (legacy filterPrice() ignores them too).
     */
    public function normalizeChoices(array $choices, array $allowedKeys): array
    {
        $clean = [];

        foreach ($choices as $key => $row) {
            if (!is_string($key) || !in_array($key, $allowedKeys, true)) {
                continue;
            }

            if (
                !is_array($row) ||
                !in_array($row['Enable'] ?? null, ['Yes', 'No'], true)
            ) {
                throw new RuntimeException('Invalid Stripe add-on selection.');
            }

            $clean[$key] = ['Enable' => $row['Enable']];
        }

        return $clean;
    }
}
