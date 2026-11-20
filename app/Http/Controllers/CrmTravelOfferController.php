<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rule;

class CrmTravelOfferController extends Controller
{
    private function guard(Request $request)
    {
        if (!$request->session()->has('crm_admin_id')) {
            return redirect()->route('crm.login');
        }

        abort_unless(
            Schema::hasTable('crm_travel_offers'),
            503,
            'Run the Travel Offers migration to enable this module.'
        );

        return null;
    }

    private function adminId(Request $request): int
    {
        return (int) $request->session()->get('crm_admin_id');
    }

    private function offerTypes(): array
    {
        return [
            'economy' => 'Economy',
            'business' => 'Business Class',
            'first' => 'First Class',
            'package' => 'Travel Package',
            'custom' => 'Custom Offer',
        ];
    }

    private function statuses(): array
    {
        return [
            'Draft',
            'Active',
            'Paused',
            'Expired',
            'Archived',
        ];
    }

    private function validationRules(): array
    {
        return [
            'title' => ['required', 'string', 'max:190'],
            'offer_type' => ['required', Rule::in(array_keys($this->offerTypes()))],
            'status' => ['required', Rule::in($this->statuses())],
            'currency' => ['required', 'string', 'max:8'],
            'price' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'cabin_class' => ['nullable', 'string', 'max:40'],
            'domestic_trips' => ['required', 'integer', 'min:0', 'max:99'],
            'international_trips' => ['required', 'integer', 'min:0', 'max:99'],
            'domestic_airlines' => ['nullable', 'string', 'max:2000'],
            'international_airline_rule' => ['nullable', 'string', 'max:5000'],
            'domestic_advance_months' => ['nullable', 'integer', 'min:0', 'max:60'],
            'international_advance_months' => ['nullable', 'integer', 'min:0', 'max:60'],
            'valid_from' => ['nullable', 'date'],
            'valid_until' => ['nullable', 'date', 'after_or_equal:valid_from'],
            'slot_limit' => ['nullable', 'integer', 'min:1', 'max:1000000'],
            'availability_message' => ['nullable', 'string', 'max:190'],
            'short_description' => ['nullable', 'string', 'max:5000'],
            'benefits' => ['nullable', 'string', 'max:50000'],
            'terms' => ['nullable', 'string', 'max:50000'],
            'banner_image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }

    private function airlinesToJson(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $airlines = collect(preg_split('/[\r\n,]+/', $value))
            ->map(fn ($airline) => trim($airline))
            ->filter()
            ->unique()
            ->values()
            ->all();

        return empty($airlines)
            ? null
            : json_encode($airlines, JSON_UNESCAPED_UNICODE);
    }

    private function airlinesForForm($value): string
    {
        if (!$value) {
            return '';
        }

        if (is_array($value)) {
            return implode(', ', $value);
        }

        $decoded = json_decode((string) $value, true);

        return is_array($decoded)
            ? implode(', ', $decoded)
            : (string) $value;
    }

    private function makeReference(): string
    {
        do {
            $reference = 'FH-OFR-' . now()->format('ymd') . '-' .
                strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
        } while (
            DB::table('crm_travel_offers')
                ->where('reference', $reference)
                ->exists()
        );

        return $reference;
    }

    public function index(Request $request)
    {
        if ($redirect = $this->guard($request)) {
            return $redirect;
        }

        $status = trim((string) $request->query('status', ''));
        $type = trim((string) $request->query('type', ''));
        $q = trim((string) $request->query('q', ''));

        $query = DB::table('crm_travel_offers')->latest();

        if ($status !== '' && in_array($status, $this->statuses(), true)) {
            $query->where('status', $status);
        }

        if ($type !== '' && array_key_exists($type, $this->offerTypes())) {
            $query->where('offer_type', $type);
        }

        if ($q !== '') {
            $query->where(function ($where) use ($q) {
                $where->where('reference', 'like', "%{$q}%")
                    ->orWhere('title', 'like', "%{$q}%")
                    ->orWhere('short_description', 'like', "%{$q}%");
            });
        }

        $offers = $query->paginate(25)->withQueryString();

        $counts = collect($this->statuses())->mapWithKeys(
            fn ($item) => [
                $item => DB::table('crm_travel_offers')
                    ->where('status', $item)
                    ->count()
            ]
        );

        return view('crm.travel-offers.index', [
            'offers' => $offers,
            'counts' => $counts,
            'types' => $this->offerTypes(),
            'statuses' => $this->statuses(),
            'activeStatus' => $status,
            'activeType' => $type,
        ]);
    }

    public function create(Request $request)
    {
        if ($redirect = $this->guard($request)) {
            return $redirect;
        }

        return view('crm.travel-offers.form', [
            'offer' => null,
            'types' => $this->offerTypes(),
            'statuses' => $this->statuses(),
            'domesticAirlines' => '',
        ]);
    }

    public function store(Request $request)
    {
        if ($redirect = $this->guard($request)) {
            return $redirect;
        }

        $data = $request->validate($this->validationRules());
        $adminId = $this->adminId($request);

        $bannerImage = null;
        if ($request->hasFile('banner_image_file')) {
            $file = $request->file('banner_image_file');
            $dir = public_path('uploads/cms');
            if (!is_dir($dir)) @mkdir($dir, 0755, true);
            $name = 'offer-banner-'.time().'-'.bin2hex(random_bytes(3)).'.'.$file->getClientOriginalExtension();
            $file->move($dir, $name);
            $bannerImage = '/uploads/cms/'.$name;
        }

        $id = DB::transaction(function () use ($data, $adminId, $bannerImage) {
            return DB::table('crm_travel_offers')->insertGetId([
                'reference' => $this->makeReference(),
                'title' => $data['title'],
                'offer_type' => $data['offer_type'],
                'status' => $data['status'],
                'currency' => strtoupper($data['currency']),
                'price' => $data['price'],
                'cabin_class' => $data['cabin_class'] ?? null,
                'domestic_trips' => $data['domestic_trips'],
                'international_trips' => $data['international_trips'],
                'domestic_airlines' => $this->airlinesToJson($data['domestic_airlines'] ?? null),
                'international_airline_rule' => $data['international_airline_rule'] ?? null,
                'domestic_advance_months' => $data['domestic_advance_months'] ?? null,
                'international_advance_months' => $data['international_advance_months'] ?? null,
                'valid_from' => $data['valid_from'] ?? null,
                'valid_until' => $data['valid_until'] ?? null,
                'slot_limit' => $data['slot_limit'] ?? null,
                'availability_message' => $data['availability_message'] ?? null,
                'short_description' => $data['short_description'] ?? null,
                'benefits' => $data['benefits'] ?? null,
                'terms' => $data['terms'] ?? null,
                'banner_image' => $bannerImage,
                'created_by' => $adminId,
                'updated_by' => $adminId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        return redirect()
            ->route('crm.travel_offers.edit', $id)
            ->with('crm_success', 'Travel offer created successfully.');
    }

    public function edit(Request $request, int $id)
    {
        if ($redirect = $this->guard($request)) {
            return $redirect;
        }

        $offer = DB::table('crm_travel_offers')->where('id', $id)->first();
        abort_unless($offer, 404);

        return view('crm.travel-offers.form', [
            'offer' => $offer,
            'types' => $this->offerTypes(),
            'statuses' => $this->statuses(),
            'domesticAirlines' => $this->airlinesForForm($offer->domestic_airlines),
        ]);
    }

    public function update(Request $request, int $id)
    {
        if ($redirect = $this->guard($request)) {
            return $redirect;
        }

        abort_unless(
            DB::table('crm_travel_offers')->where('id', $id)->exists(),
            404
        );

        $data = $request->validate($this->validationRules());

        $currentOffer = DB::table('crm_travel_offers')->where('id', $id)->first();
        $bannerImage = $currentOffer->banner_image;

        if ($request->hasFile('banner_image_file')) {
            $file = $request->file('banner_image_file');
            $dir = public_path('uploads/cms');
            if (!is_dir($dir)) @mkdir($dir, 0755, true);
            $name = 'offer-banner-'.time().'-'.bin2hex(random_bytes(3)).'.'.$file->getClientOriginalExtension();
            $file->move($dir, $name);
            $bannerImage = '/uploads/cms/'.$name;
        }

        DB::table('crm_travel_offers')
            ->where('id', $id)
            ->update([
                'title' => $data['title'],
                'offer_type' => $data['offer_type'],
                'status' => $data['status'],
                'currency' => strtoupper($data['currency']),
                'price' => $data['price'],
                'cabin_class' => $data['cabin_class'] ?? null,
                'domestic_trips' => $data['domestic_trips'],
                'international_trips' => $data['international_trips'],
                'domestic_airlines' => $this->airlinesToJson($data['domestic_airlines'] ?? null),
                'international_airline_rule' => $data['international_airline_rule'] ?? null,
                'domestic_advance_months' => $data['domestic_advance_months'] ?? null,
                'international_advance_months' => $data['international_advance_months'] ?? null,
                'valid_from' => $data['valid_from'] ?? null,
                'valid_until' => $data['valid_until'] ?? null,
                'slot_limit' => $data['slot_limit'] ?? null,
                'availability_message' => $data['availability_message'] ?? null,
                'short_description' => $data['short_description'] ?? null,
                'benefits' => $data['benefits'] ?? null,
                'terms' => $data['terms'] ?? null,
                'banner_image' => $bannerImage,
                'updated_by' => $this->adminId($request),
                'updated_at' => now(),
            ]);

        return redirect()
            ->route('crm.travel_offers.edit', $id)
            ->with('crm_success', 'Travel offer updated successfully.');
    }

    public function preview(Request $request, int $id)
    {
        if ($redirect = $this->guard($request)) {
            return $redirect;
        }

        $offer = DB::table('crm_travel_offers')->where('id', $id)->first();
        abort_unless($offer, 404);

        $airlines = json_decode((string) $offer->domestic_airlines, true);

        if (!is_array($airlines)) {
            $airlines = [];
        }

        return view('crm.travel-offers.preview', compact('offer', 'airlines'));
    }

    public function sendForm(Request $request, int $id)
    {
        if ($redirect = $this->guard($request)) {
            return $redirect;
        }

        $offer = DB::table('crm_travel_offers')->where('id', $id)->first();
        abort_unless($offer, 404);

        return view('crm.travel-offers.send', compact('offer'));
    }

    public function send(Request $request, int $id)
    {
        if ($redirect = $this->guard($request)) {
            return $redirect;
        }

        $offer = DB::table('crm_travel_offers')->where('id', $id)->first();
        abort_unless($offer, 404);

        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:150'],
            'customer_email' => ['required', 'email', 'max:190'],
            'customer_phone' => ['nullable', 'string', 'max:50'],
            'subject' => ['required', 'string', 'max:255'],
        ]);

        $email = strtolower(trim($data['customer_email']));

        $snapshot = [
            'reference' => $offer->reference,
            'title' => $offer->title,
            'offer_type' => $offer->offer_type,
            'currency' => $offer->currency,
            'price' => $offer->price,
            'cabin_class' => $offer->cabin_class,
            'domestic_trips' => $offer->domestic_trips,
            'international_trips' => $offer->international_trips,
            'domestic_airlines' => json_decode((string) $offer->domestic_airlines, true) ?: [],
            'international_airline_rule' => $offer->international_airline_rule,
            'domestic_advance_months' => $offer->domestic_advance_months,
            'international_advance_months' => $offer->international_advance_months,
            'valid_from' => $offer->valid_from,
            'valid_until' => $offer->valid_until,
            'slot_limit' => $offer->slot_limit,
            'availability_message' => $offer->availability_message,
            'short_description' => $offer->short_description,
            'benefits' => $offer->benefits,
            'terms' => $offer->terms,
            'banner_image' => $offer->banner_image,
        ];

        $version = ((int) DB::table('crm_travel_offer_versions')
            ->where('travel_offer_id', $offer->id)
            ->max('version')) + 1;

        $versionId = DB::table('crm_travel_offer_versions')->insertGetId([
            'travel_offer_id' => $offer->id,
            'version' => $version,
            'customer_name' => trim($data['customer_name']),
            'customer_email' => $email,
            'customer_phone' => trim((string) ($data['customer_phone'] ?? '')) ?: null,
            'subject' => trim($data['subject']),
            'offer_snapshot' => json_encode($snapshot, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            'status' => 'Prepared',
            'expires_at' => $offer->valid_until
                ? \Carbon\Carbon::parse($offer->valid_until)->endOfDay()
                : null,
            'created_by' => $this->adminId($request),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $customerUrl = URL::temporarySignedRoute(
            'travel_offer.customer',
            $offer->valid_until
                ? \Carbon\Carbon::parse($offer->valid_until)->endOfDay()
                : now()->addDays(30),
            ['version' => $versionId]
        );

        $currencySymbols = [
            'USD' => '$',
            'CAD' => 'C$',
            'GBP' => '£',
            'EUR' => '€',
            'INR' => '₹',
        ];

        $symbol = $currencySymbols[$offer->currency] ?? ($offer->currency . ' ');
        $price = $symbol . number_format((float) $offer->price, 0);

        $message = view('crm.travel-offers.email', [
            'offer' => $offer,
            'customerName' => trim($data['customer_name']),
            'customerUrl' => $customerUrl,
            'price' => $price,
        ])->render();

        $sent = (bool) app(\App\Http\Controllers\FlightController::class)
            ->sendSmptEmail($email, trim($data['subject']), $message);

        if (!$sent) {
            return back()
                ->withInput()
                ->with('crm_error', 'The offer was prepared, but the email could not be sent. Please verify the mail service and try again.');
        }

        DB::table('crm_travel_offer_versions')
            ->where('id', $versionId)
            ->update([
                'status' => 'Sent',
                'sent_at' => now(),
                'updated_at' => now(),
            ]);

        return redirect()
            ->route('crm.travel_offers.edit', $offer->id)
            ->with('crm_success', 'Travel offer sent successfully to ' . $email . '.');
    }

    private function loadCustomerOfferVersion(int $version): array
    {
        $record = DB::table('crm_travel_offer_versions')
            ->where('id', $version)
            ->first();

        abort_unless($record, 404);
        abort_unless(in_array($record->status, ['Sent', 'Viewed', 'Accepted'], true), 404);

        if ($record->expires_at && now()->greaterThan(\Carbon\Carbon::parse($record->expires_at))) {
            abort(410, 'This travel offer has expired.');
        }

        $snapshot = json_decode((string) $record->offer_snapshot, true);
        abort_unless(is_array($snapshot), 404);

        return [$record, $snapshot];
    }

    public function bookSlot(Request $request, int $version)
    {
        [$record, $snapshot] = $this->loadCustomerOfferVersion($version);

        return view('travel-offer.book', [
            'version' => $record,
            'offer' => (object) $snapshot,
            'airlines' => $snapshot['domestic_airlines'] ?? [],
        ]);
    }

    public function reviewSlot(Request $request, int $version)
    {
        [$record, $snapshot] = $this->loadCustomerOfferVersion($version);

        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:150'],
            'customer_email' => ['required', 'email', 'max:190'],
            'customer_phone' => ['nullable', 'string', 'max:80'],
            'details_later' => ['nullable', 'boolean'],
            'trips' => ['nullable', 'array'],
            'trips.*.origin' => ['nullable', 'string', 'max:10'],
            'trips.*.destination' => ['nullable', 'string', 'max:10'],
            'trips.*.departure_date' => ['nullable', 'date'],
            'trips.*.return_date' => ['nullable', 'date'],
        ]);

        $data['customer_email'] = strtolower(trim($data['customer_email']));
        $data['customer_name'] = trim($data['customer_name']);
        $data['customer_phone'] = trim((string) ($data['customer_phone'] ?? '')) ?: null;
        $data['details_later'] = !empty($data['details_later']);

        foreach (($data['trips'] ?? []) as $key => &$trip) {
            foreach (['origin', 'destination'] as $field) {
                $trip[$field] = strtoupper(trim((string) ($trip[$field] ?? ''))) ?: null;
            }
            foreach (['departure_date', 'return_date'] as $field) {
                $trip[$field] = $trip[$field] ?? null;
            }
        }
        unset($trip);

        return view('travel-offer.review', [
            'version' => $record,
            'offer' => (object) $snapshot,
            'data' => $data,
        ]);
    }

    public function confirmSlot(Request $request, int $version)
    {
        [$record, $snapshot] = $this->loadCustomerOfferVersion($version);

        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:150'],
            'customer_email' => ['required', 'email', 'max:190'],
            'customer_phone' => ['nullable', 'string', 'max:80'],
            'details_later' => ['nullable', 'boolean'],
            'agreement' => ['accepted'],
            'trips' => ['nullable', 'array'],
            'trips.*.origin' => ['nullable', 'string', 'max:10'],
            'trips.*.destination' => ['nullable', 'string', 'max:10'],
            'trips.*.departure_date' => ['nullable', 'date'],
            'trips.*.return_date' => ['nullable', 'date'],
        ]);

        $email = strtolower(trim($data['customer_email']));
        $name = trim($data['customer_name']);
        $phone = trim((string) ($data['customer_phone'] ?? '')) ?: null;
        $submittedTrips = $data['trips'] ?? [];

        $planId = DB::transaction(function () use ($version, $record, $snapshot, $email, $name, $phone, $submittedTrips) {
            $lockedVersion = DB::table('crm_travel_offer_versions')
                ->where('id', $version)
                ->lockForUpdate()
                ->first();

            abort_unless($lockedVersion, 404);

            $existing = DB::table('crm_travel_plans')
                ->where('offer_version_id', $version)
                ->first();

            if ($existing) {
                return (int) $existing->id;
            }

            do {
                $reference = 'FH-PLAN-' . now()->format('ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
            } while (DB::table('crm_travel_plans')->where('reference', $reference)->exists());

            $planId = DB::table('crm_travel_plans')->insertGetId([
                'reference' => $reference,
                'travel_offer_id' => $record->travel_offer_id,
                'offer_version_id' => $record->id,
                'customer_name' => $name,
                'customer_email' => $email,
                'customer_phone' => $phone,
                'status' => 'Pending Payment',
                'payment_status' => 'Unpaid',
                'currency' => $snapshot['currency'] ?? 'USD',
                'sale_amount' => (float) ($snapshot['price'] ?? 0),
                'amount_paid' => 0,
                'total_cost' => 0,
                'starts_on' => $snapshot['valid_from'] ?? null,
                'expires_on' => $snapshot['valid_until'] ?? null,
                'terms_version' => 'offer-version-' . $record->version,
                'agreement_accepted_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $domesticAirlines = $snapshot['domestic_airlines'] ?? [];

            for ($i = 1; $i <= (int) ($snapshot['domestic_trips'] ?? 0); $i++) {
                $trip = $submittedTrips['domestic_' . $i] ?? [];

                DB::table('crm_travel_plan_trips')->insert([
                    'travel_plan_id' => $planId,
                    'trip_type' => 'Domestic',
                    'sequence' => $i,
                    'title' => 'Domestic Round Trip #' . $i,
                    'status' => 'Available',
                    'cabin_class' => $snapshot['cabin_class'] ?? null,
                    'eligible_airlines' => json_encode($domesticAirlines),
                    'advance_booking_months' => $snapshot['domestic_advance_months'] ?? null,
                    'origin' => !empty($trip['origin']) ? strtoupper(trim($trip['origin'])) : null,
                    'destination' => !empty($trip['destination']) ? strtoupper(trim($trip['destination'])) : null,
                    'departure_date' => $trip['departure_date'] ?? null,
                    'return_date' => $trip['return_date'] ?? null,
                    'ticket_cost' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            for ($i = 1; $i <= (int) ($snapshot['international_trips'] ?? 0); $i++) {
                $trip = $submittedTrips['international_' . $i] ?? [];

                DB::table('crm_travel_plan_trips')->insert([
                    'travel_plan_id' => $planId,
                    'trip_type' => 'International',
                    'sequence' => $i,
                    'title' => 'International Round Trip #' . $i,
                    'status' => 'Available',
                    'cabin_class' => $snapshot['cabin_class'] ?? null,
                    'eligible_airlines' => null,
                    'advance_booking_months' => $snapshot['international_advance_months'] ?? null,
                    'origin' => !empty($trip['origin']) ? strtoupper(trim($trip['origin'])) : null,
                    'destination' => !empty($trip['destination']) ? strtoupper(trim($trip['destination'])) : null,
                    'departure_date' => $trip['departure_date'] ?? null,
                    'return_date' => $trip['return_date'] ?? null,
                    'ticket_cost' => 0,
                    'notes' => $snapshot['international_airline_rule'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::table('crm_travel_plan_activity')->insert([
                'travel_plan_id' => $planId,
                'activity_type' => 'Plan Created',
                'title' => 'Travel Plan request confirmed by customer',
                'details' => 'Created from offer ' . ($snapshot['reference'] ?? ''),
                'meta_json' => json_encode(['offer_version_id' => $record->id]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('crm_travel_offer_versions')
                ->where('id', $record->id)
                ->update([
                    'status' => 'Accepted',
                    'accepted_at' => now(),
                    'updated_at' => now(),
                ]);

            return (int) $planId;
        });

        $plan = DB::table('crm_travel_plans')->where('id', $planId)->first();

        return view('travel-offer.confirmed', [
            'plan' => $plan,
            'offer' => (object) $snapshot,
        ]);
    }

    public function customerView(Request $request, int $version)
    {
        $record = DB::table('crm_travel_offer_versions')
            ->where('id', $version)
            ->first();

        abort_unless($record, 404);
        abort_unless(in_array($record->status, ['Sent', 'Viewed', 'Accepted'], true), 404);

        if ($record->expires_at && now()->greaterThan(\Carbon\Carbon::parse($record->expires_at))) {
            abort(410, 'This travel offer has expired.');
        }

        $snapshot = json_decode((string) $record->offer_snapshot, true);
        abort_unless(is_array($snapshot), 404);

        if ($record->status === 'Sent') {
            DB::table('crm_travel_offer_versions')
                ->where('id', $record->id)
                ->update([
                    'status' => 'Viewed',
                    'viewed_at' => now(),
                    'updated_at' => now(),
                ]);

            $record->status = 'Viewed';
            $record->viewed_at = now();
        }

        return view('travel-offer.customer', [
            'version' => $record,
            'offer' => (object) $snapshot,
            'airlines' => $snapshot['domestic_airlines'] ?? [],
        ]);
    }

}
