<?php

namespace App\Services;

use App\Http\Controllers\FlightController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use RuntimeException;

/**
 * Step F3 (website flow): the Stripe booking form.
 *  - Same customer checks as legacy FlightController::submitBooking() MINUS the
 *    card fields (Stripe collects the card on its own page).
 *  - Builds the snapshot the booking executor (F3b) uses after the webhook,
 *    because the webhook has no customer session.
 *  - Card fields are never read, kept or stored here.
 */
class StripeBookingFormService
{
    public const CARD_FIELDS = ['creditcard_no', 'cardholder_name', 'expire_month', 'expire_year', 'cvv'];
    public const MAX_PAX = 9;
    private const NAME = "regex:/^[\\p{L}\\p{M} .'’\\-]+$/u";

    public function rules(Request $request, string $flow = 'website'): array
    {
        $rules = [
            'code' => ['required', 'string', 'regex:/^[a-zA-Z0-9_-]{1,128}$/D'],
            'key' => ['required', 'string', 'regex:/^[a-zA-Z0-9_-]{1,64}$/D'],
            'loop' => ['required', 'integer', 'min:0', 'max:'.(self::MAX_PAX - 1)],
            'OfferCode' => ['required', 'string', 'max:5000'],
            'SearchGuid' => ['required', 'string', 'max:255'],
            'flight_emailid' => 'required|email',
            'flight_mobile' => 'required|digits:10',
            'flight_mobile_telcode' => 'nullable|string|max:10',
            'billing_address' => 'required|string|max:500',
            'billing_country' => 'required|string|max:100',
            'billing_state_97' => 'required_if:billing_country,United States|nullable|string|max:100',
            'billing_state' => 'required_unless:billing_country,United States|nullable|string|max:100',
            'billing_city' => 'required|string|max:100',
            'billing_pincode' => 'required|string|max:20',
            'billing_mobile_telcode' => 'nullable|string|max:10',
            'billing_mobile' => 'required|string|max:20',
            'i_field_encode' => 'nullable|string|max:20000',
        ];

        // Step FL: the legacy FareLeaders form never required billing_state.
        if ($flow === 'fareleaders') {
            $rules['billing_state'] = 'nullable|string|max:100';
            $rules['priceinfo'] = 'nullable|string|max:5000';
            $rules['field'] = 'nullable|string|max:5000';
        }

        $loop = min(max(0, (int) $request->input('loop', 0)), self::MAX_PAX - 1);

        for ($k = 0; $k <= $loop; $k++) {
            $rules['adult_fname_'.$k] = ['required', 'string', 'max:50', self::NAME];
            $rules['adult_lname_'.$k] = ['required', 'string', 'max:50', self::NAME];
            $rules['adult_mname_'.$k] = ['nullable', 'string', 'max:50', self::NAME];
            $rules['AgeType_'.$k] = ['nullable', 'string', 'max:30'];
            $rules['adult_title_'.$k] = ['nullable', 'string', 'max:30'];
            $rules['adult_gender_'.$k] = ['nullable', 'string', 'max:30'];
            $rules['adult_dob_'.$k] = ['nullable', 'string', 'max:30'];
        }

        return $rules;
    }

    /** Add-on Yes/No choices: same hidden "service" field the legacy form posts. */
    public function choices($service): array
    {
        if ($service === null || $service === '') {
            return [];
        }
        if (is_array($service)) {
            return $service;
        }
        if (!is_string($service) || strlen($service) > 8000) {
            throw new RuntimeException('Invalid Stripe add-on selection.');
        }
        try {
            $data = json_decode($service, true, 4, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new RuntimeException('Invalid Stripe add-on selection.');
        }
        if (!is_array($data)) {
            throw new RuntimeException('Invalid Stripe add-on selection.');
        }

        return $data;
    }

    /** Trusted server-side search data for this code; null when the session is gone. */
    public function sessionContext(string $code, string $flow = 'website'): ?array
    {
        $flight = app(FlightController::class);
        if (!$flight->isSessionCode($code)) {
            return null;
        }

        if ($flow === 'fareleaders') {
            // Step FL: data saved by FareleaderController::flightBooking() for this page code.
            return [
                'flow' => 'fareleaders',
                'offer' => Session::get($code.'.FL-OfferCode'),
                'guid' => Session::get($code.'.FL-SearchGuid'),
                'fields' => $flight->getSearchField($code),
                'fl_price_markup' => Session::get($code.'.FL-priceMarkup'),
                'fl_info_markup' => Session::get($code.'.FL-infoMarkup'),
                'partner_source' => Session::get($code.'.FL-partner-source'),
            ];
        }

        return [
            'flow' => 'website',
            'offer' => Session::get($code.'.Selected-OfferCode'),
            'guid' => Session::get($code.'.Search-SearchGuid'),
            'fields' => $flight->getSearchField($code),
            'min_markup' => Session::get($code.'.Min-Markup'),
            'price_info' => Session::get($code.'.priceInfo'),
            'selected' => Session::get($code.'.Selected-Result'),
        ];
    }

    public function buildSnapshot(Request $request, array $ctx, array $fare, array $choices): array
    {
        $offer = $ctx['offer'] ?? null;
        $guid = $ctx['guid'] ?? null;

        if (
            !is_string($offer) || $offer === '' || !is_string($guid) || $guid === '' ||
            !hash_equals($offer, (string) $request->input('OfferCode')) ||
            !hash_equals($guid, (string) $request->input('SearchGuid'))
        ) {
            throw new RuntimeException('Stripe booking flight does not match the search session.');
        }

        $fields = is_array($ctx['fields'] ?? null) ? $ctx['fields'] : [];
        $expected = (int) ($fields['Adults'] ?? 0) + (int) ($fields['Childrens'] ?? 0) + (int) ($fields['Infants'] ?? 0);
        $loop = (int) $request->input('loop', -1);

        if ($expected < 1 || $expected > self::MAX_PAX || $loop + 1 !== $expected) {
            throw new RuntimeException('Stripe booking passenger count does not match the search.');
        }

        $flow = $ctx['flow'] ?? 'website';

        if (
            !in_array($flow, ['website', 'fareleaders'], true) ||
            ($fare['flow'] ?? null) !== $flow ||
            !is_int($fare['amount_cents'] ?? null) || $fare['amount_cents'] < 50
        ) {
            throw new RuntimeException('Stripe booking fare is invalid.');
        }

        // PaxData exactly as legacy submitBooking() builds it for AeroBook.
        $pax = [];
        $string = '';
        for ($k = 0; $k <= $loop; $k++) {
            $string = (string) random_int(100, 999);
            $pax[$k] = [
                'AgeType' => $request->input('AgeType_'.$k),
                'Title' => $request->input('adult_title_'.$k),
                'BirthDay' => date('d.m.Y', (int) strtotime((string) $request->input('adult_dob_'.$k))),
                'BirthISO' => 'RUS',
                'Document' => 'AgeType_'.$k.$string.'doc'.$string,
                'GenderType' => $request->input('adult_gender_'.$k),
                'MiddleName' => $request->input('adult_mname_'.$k),
                'Name' => $request->input('adult_fname_'.$k),
                'Surname' => $request->input('adult_lname_'.$k),
            ];
        }

        $minMarkup = is_array($ctx['min_markup'] ?? null) ? $ctx['min_markup'] : [];
        $selected = is_array($ctx['selected'] ?? null) ? $ctx['selected'] : [];
        $email = (string) $request->input('flight_emailid');

        if ($flow === 'fareleaders') {
            // Same values legacy submitBookingFLeader() stores (is_markup is not set there).
            $priceMarkup = is_array($ctx['fl_price_markup'] ?? null) ? $ctx['fl_price_markup'] : null;
            $infoMarkup = is_array($ctx['fl_info_markup'] ?? null) ? $ctx['fl_info_markup'] : null;
            $isMarkup = null;
            $partner = $ctx['partner_source'] ?? null;
            $source = in_array($partner, ['FareLeaders Core', 'FareLeaders Compare'], true) ? $partner : 'FareLeader';
        } else {
            $priceMarkup = is_array($ctx['price_info'] ?? null) ? $ctx['price_info'] : null;
            $infoMarkup = $selected['priceMarkup'] ?? null;
            $isMarkup = (float) ($minMarkup['TotalPrice'] ?? 0) > 0 ? 'Yes' : 'No';
            $source = (($fields['Source'] ?? '') === 'FareLeader') ? 'FareLeader' : 'Website';
        }

        return [
            'flow' => $flow,
            'search_code' => (string) $request->input('code'),
            'search_key' => (string) $request->input('key'),
            'offer_code' => $offer,
            'search_guid' => $guid,
            'client_reference' => $string.$email,
            'contact' => [
                'email' => $email,
                'mobile' => (string) $request->input('flight_mobile'),
                'mobile_telcode' => (string) $request->input('flight_mobile_telcode', ''),
            ],
            'passengers' => $pax,
            'billing' => $this->billing($request),
            'price' => [
                'amount_cents' => $fare['amount_cents'],
                'currency' => 'usd',
                'supplier_expected_cents' => $fare['supplier_expected_cents'] ?? null,
                'service_p' => $fare['service_pricing'] ?? null,
                'full_p' => number_format($fare['amount_cents'] / 100, 2, '.', ''),
                'is_markup' => $isMarkup,
                'price_markup' => $priceMarkup,
                'info_markup' => $infoMarkup,
                'choices' => $choices,
            ],
            'search_fields' => $fields,
            'i_field_encode' => (string) $request->input('i_field_encode', ''),
            'source' => $source,
            'created_at' => now()->toIso8601String(),
        ];
    }

    /** Form refill data for the booking page (legacy Booking-Form) - without card fields. */
    public function sessionForm(Request $request): array
    {
        $temp = [];
        $loop = min(max(0, (int) $request->input('loop', 0)), self::MAX_PAX - 1);
        for ($k = 0; $k <= $loop; $k++) {
            $temp['adult_title_'.$k] = $request->input('adult_title_'.$k);
            $temp['adult_fname_'.$k] = $request->input('adult_fname_'.$k);
            $temp['adult_mname_'.$k] = $request->input('adult_mname_'.$k);
            $temp['adult_lname_'.$k] = $request->input('adult_lname_'.$k);
            $temp['adult_gender_'.$k] = $request->input('adult_gender_'.$k);
            $temp['adult_dob_'.$k] = date('Y-m-d', (int) strtotime((string) $request->input('adult_dob_'.$k)));
        }
        $temp['flight_emailid'] = $request->input('flight_emailid');
        $temp['flight_mobile_telcode'] = $request->input('flight_mobile_telcode');
        $temp['flight_mobile'] = $request->input('flight_mobile');

        return $temp + $this->billing($request);
    }

    private function billing(Request $request): array
    {
        $out = [];
        foreach (['billing_address', 'billing_country', 'billing_state', 'billing_state_97', 'billing_city',
            'billing_pincode', 'billing_mobile_telcode', 'billing_mobile'] as $f) {
            $out[$f] = $request->input($f);
        }

        return $out;
    }
}
