<?php

namespace App\Services;

use App\Http\Controllers\FlightController;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use RuntimeException;

/**
 * Stripe-only: fresh supplier quote before any Stripe session is created.
 *
 * Calls the EXISTING FlightController::PreBookApi() (AeroPrebook - no booking
 * is made) for the customer's selected website offer, validates the real
 * response shape (StripeTestSupplierFareService) and requires the supplier
 * FullPrice to equal the raw supplier total we priced from.
 *
 * Legacy booking / prebook code is not modified.
 */
class StripeTestSupplierQuoteService
{
    public const FARE_CHANGED = 409;

    /** Speed (17 Nov 2026): a Prebook answer younger than this (same offer + guid) is reused at Pay. */
    public const PREBOOK_REUSE_SECONDS = 180;

    public function verify(string $code, array $fare): array
    {
        $flow = $fare['flow'] ?? null;

        if (!in_array($flow, ['website', 'fareleaders'], true)) {
            throw new RuntimeException('Unsupported Stripe supplier quote flow.');
        }

        $expected = $fare['supplier_expected_cents'] ?? null;

        // Website: always required. FareLeaders: null allowed (no page-open
        // supplier price yet) -> this fresh quote becomes the reference.
        if (
            ($flow === 'website' || $expected !== null) &&
            (!is_int($expected) || $expected < 50)
        ) {
            throw new RuntimeException('Stripe expected supplier fare is missing.');
        }

        if ($flow === 'fareleaders') {
            $offer = Session::get($code.'.FL-OfferCode');
            $guid = Session::get($code.'.FL-SearchGuid');
        } else {
            $offer = Session::get($code.'.Selected-OfferCode');
            $guid = Session::get($code.'.Search-SearchGuid');
        }

        if (!is_string($offer) || $offer === '' || !is_string($guid) || $guid === '') {
            throw new RuntimeException('Stripe supplier quote reference is missing.');
        }

        $controller = app(FlightController::class);
        $fields = $controller->getSearchField($code);

        // Speed (17 Nov 2026): reuse a very recent successful Prebook of the SAME offer (made when
        // the customer selected the flight or reached the payment step). The executor still does
        // its own fresh Prebook after payment, right before AeroBook. One use only.
        $cached = $flow === 'website' ? Session::get($code.'.FH-Prebook') : null;
        if (
            is_array($cached) && is_array($cached['result'] ?? null)
            && hash_equals((string) ($cached['offer'] ?? ''), $offer)
            && hash_equals((string) ($cached['guid'] ?? ''), $guid)
            && (time() - (int) ($cached['at'] ?? 0)) >= 0
            && (time() - (int) ($cached['at'] ?? 0)) <= self::PREBOOK_REUSE_SECONDS
            && (string) ($cached['result']['Success'] ?? '') === '1'
        ) {
            Session::forget($code.'.FH-Prebook');
            Log::info('FARESHUNT_STRIPE_QUOTE_REUSED', ['search_code' => $code, 'age_s' => time() - (int) $cached['at']]);
            $output = ['status' => '1', 'result' => $cached['result']];
        } else {
            $output = $controller->PreBookApi($offer, $guid);
        }

        if ((string) ($output['status'] ?? '0') !== '1') {
            Log::warning('FARESHUNT_STRIPE_TEST_QUOTE_FAILED', [
                'search_code' => $code,
                'error_code' => (string) ($output['errorCode'] ?? ''),
            ]);

            throw new RuntimeException('Supplier fare is no longer available.', self::FARE_CHANGED);
        }

        $result = json_decode(json_encode($output['result'] ?? null), true);

        if (!is_array($result)) {
            throw new RuntimeException('Supplier quote response is invalid.');
        }

        if ($expected === null) {
            return $this->fresh($result, $fields);
        }

        return $this->compare($result, $fields, $expected, $code);
    }

    /**
     * Step FL: FareLeaders without a page-open supplier price. Validates the
     * fresh AeroPrebook (USD, breakdown = FullPrice) and returns it as the
     * expected supplier amount. Pure - unit-testable.
     */
    public function fresh(array $prebook, array $fields): array
    {
        $supplier = app(StripeTestSupplierFareService::class)->validate($prebook, $fields);

        return [
            'currency' => 'usd',
            'supplier_amount_cents' => $supplier['supplier_amount_cents'],
            'book_limit' => is_string($prebook['BookLimit'] ?? null)
                ? mb_substr($prebook['BookLimit'], 0, 40)
                : null,
        ];
    }

    /**
     * Pure comparison (no external call) - unit-testable.
     */
    public function compare(array $prebook, array $fields, int $expectedCents, string $code = ''): array
    {
        $supplier = app(StripeTestSupplierFareService::class)->validate($prebook, $fields);

        if ($supplier['supplier_amount_cents'] !== $expectedCents) {
            // Owner decision 17 Nov 2026: the customer always pays the price shown on our site,
            // even if the supplier price changed since the search. The fresh supplier price is
            // kept on the attempt (and in the snapshot) so the difference is visible in logs/reports.
            Log::warning('FARESHUNT_STRIPE_FARE_CHANGED_ACCEPTED', [
                'search_code' => $code,
                'expected_cents' => $expectedCents,
                'supplier_cents' => $supplier['supplier_amount_cents'],
                'diff_cents' => $supplier['supplier_amount_cents'] - $expectedCents,
            ]);
        }

        return [
            'currency' => 'usd',
            'supplier_amount_cents' => $supplier['supplier_amount_cents'],
            'book_limit' => is_string($prebook['BookLimit'] ?? null)
                ? mb_substr($prebook['BookLimit'], 0, 40)
                : null,
        ];
    }
}
