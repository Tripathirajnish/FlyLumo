<?php

namespace App\Services;

use App\Models\Booking;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;

/**
 * Step F3b: book the flight AFTER Stripe has put the money on hold (owner-approved D3/D4/D5a/D7).
 *
 *  - Runs only for attempts the signature-verified webhook marked "authorized".
 *  - One run per attempt: booking_status not_started -> booking is a compare-and-set,
 *    and the snapshot can be consumed only once. AeroBook is NEVER retried automatically.
 *  - D4: fresh AeroPrebook first. Fare gone / changed -> Stripe hold released, nothing booked.
 *  - D7: any unclear outcome (exception, timeout, not confirmed) -> booking_status manual_review,
 *    hold kept, the existing alert email tells the team (status authorized + error code).
 *  - D5a: o_Credit holds only payment_method, brand, last4 (from Stripe), Stripe ref, attempt id.
 *  - Payment is NOT captured here; capture/cancel happens later from the CRM.
 */
class StripeBookingExecutorService
{
    public const STUCK_BOOKING_MIN = 10;

    public function runDue(int $limit = 5): array
    {
        $out = [];

        // A run that died half-way: never run AeroBook again, hand it to a human.
        $stuck = DB::table('stripe_payment_attempts')
            ->where('status', 'authorized')->where('booking_status', 'booking')
            ->where('updated_at', '<', now()->subMinutes(self::STUCK_BOOKING_MIN))
            ->orderBy('id')->limit(20)->pluck('id');
        foreach ($stuck as $id) {
            $out[(int) $id] = $this->review((int) $id, 'booking_interrupted');
        }

        $ids = DB::table('stripe_payment_attempts')
            ->where('status', 'authorized')->where('booking_status', 'not_started')->whereIn('flow', ['website', 'fareleaders'])
            ->orderBy('id')->limit(max(1, min(20, $limit)))->pluck('id');
        foreach ($ids as $id) {
            $out[(int) $id] = $this->run((int) $id);
        }

        return $out;
    }

    public function run(int $attemptId): string
    {
        $claimed = DB::table('stripe_payment_attempts')
            ->where('id', $attemptId)->where('status', 'authorized')->where('booking_status', 'not_started')
            ->update(['booking_status' => 'booking', 'updated_at' => now()]) === 1;

        if (!$claimed) {
            return 'skipped';
        }

        try {
            return $this->book($attemptId);
        } catch (\Throwable $e) {
            return $this->review($attemptId, 'executor_error', ['error' => get_class($e)]);
        }
    }

    private function book(int $attemptId): string
    {
        $attempt = DB::table('stripe_payment_attempts')->where('id', $attemptId)->first();
        $snap = app(StripeBookingSnapshotService::class)->load($attemptId);

        if (!$snap) {
            return $this->review($attemptId, 'snapshot_missing');
        }
        if (!is_string($attempt->stripe_payment_intent_id) || !preg_match('/^pi_[A-Za-z0-9]+$/D', $attempt->stripe_payment_intent_id)) {
            return $this->review($attemptId, 'payment_intent_missing');
        }
        if ((int) ($snap['price']['amount_cents'] ?? -1) !== (int) $attempt->amount_cents) {
            return $this->review($attemptId, 'snapshot_amount_mismatch');
        }
        $expected = $snap['price']['supplier_expected_cents'] ?? null;
        if (!is_int($expected) || $expected < 50) {
            return $this->review($attemptId, 'supplier_expected_missing');
        }

        $flight = $this->flight();

        // D4: fresh supplier quote right before booking (AeroPrebook makes no booking).
        try {
            $pre = $flight->PreBookApi($snap['offer_code'], $snap['search_guid']);
        } catch (\Throwable $e) {
            return $this->review($attemptId, 'prebook_error', ['error' => get_class($e)]);
        }
        if ((string) ($pre['status'] ?? '0') !== '1') {
            return $this->releaseHold($attempt, $snap, 'fare_unavailable');
        }
        try {
            $preResult = json_decode(json_encode($pre['result'] ?? null), true);
            $supplierCents = $this->supplierCents(is_array($preResult) ? $preResult : [], (array) ($snap['search_fields'] ?? []));
        } catch (\Throwable $e) {
            return $this->review($attemptId, 'prebook_invalid', ['error' => get_class($e)]);
        }
        if ($supplierCents !== $expected) {
            // Owner decision 17 Nov 2026: book at the price the customer saw (amount_cents is
            // unchanged); the supplier difference is logged, the booking is NOT stopped.
            Log::warning('FARESHUNT_STRIPE_BOOKING_FARE_CHANGED_ACCEPTED', ['attempt_id' => $attemptId, 'expected_cents' => $expected, 'supplier_cents' => $supplierCents, 'diff_cents' => $supplierCents - $expected]);
            $expected = $supplierCents;
        }

        // Safety switch: no real supplier booking until the owner enables it (F6).
        if (config('stripe_checkout.executor_book_enabled') !== true) {
            return $this->review($attemptId, 'aerobook_disabled');
        }

        if (!app(StripeBookingSnapshotService::class)->consume($attemptId)) {
            return $this->review($attemptId, 'snapshot_already_used');
        }

        $params = [
            'ClientReference' => $snap['client_reference'],
            'Email' => $snap['contact']['email'],
            'Phone' => $snap['contact']['mobile'],
            'OfferCode' => $snap['offer_code'],
            'SearchGuid' => $snap['search_guid'],
        ];
        $params['PaxList']['PaxData'] = $snap['passengers'];

        $started = microtime(true);
        try {
            $output = $flight->BookApi($params);
        } catch (\Throwable $e) {
            return $this->review($attemptId, 'aerobook_exception', ['error' => get_class($e)]);
        }

        $status = (string) ($output['status'] ?? '');
        $result = json_decode(json_encode($output['result'] ?? null), true);
        Log::info('FARESHUNT_STRIPE_AEROBOOK_OUTCOME', [
            'attempt_id' => $attemptId,
            'status' => $status,
            'error_code' => (string) ($output['errorCode'] ?? ''),
            'has_result' => !empty($result),
            'book_api_ms' => (int) round((microtime(true) - $started) * 1000),
        ]);

        if ($status !== '1' || !is_array($result) || (string) ($result['Success'] ?? '') !== '1') {
            return $this->review($attemptId, 'aerobook_not_confirmed', ['error_code' => (string) ($output['errorCode'] ?? '')]);
        }

        $fullCents = $this->cents($result['FullPrice'] ?? null);
        $mismatch = $fullCents !== $expected;

        try {
            $bookingId = DB::transaction(function () use ($attempt, $snap, $result, $flight, $mismatch) {
                $id = $this->saveBooking($this->buildBooking($attempt, $snap, $result, $flight));
                DB::table('stripe_payment_attempts')->where('id', $attempt->id)->where('booking_status', 'booking')->update([
                    'booking_id' => $id,
                    'booking_status' => $mismatch ? 'manual_review' : 'booked',
                    'last_error_code' => $mismatch ? 'aerobook_price_mismatch' : null,
                    'updated_at' => now(),
                ]);

                return $id;
            });
        } catch (\Throwable $e) {
            // Supplier HAS booked but our row failed: a human must finish it.
            Log::critical('FARESHUNT_STRIPE_BOOKING_SAVE_FAILED', [
                'attempt_id' => $attemptId,
                'book_id' => (string) ($result['BookId'] ?? ''),
                'error' => get_class($e),
            ]);
            return $this->review($attemptId, 'booking_save_failed');
        }

        Log::info('FARESHUNT_STRIPE_BOOKING_CREATED', ['attempt_id' => $attemptId, 'booking_id' => $bookingId, 'price_mismatch' => $mismatch]);

        try {
            $flight->SendingMail($bookingId);
        } catch (\Throwable $e) {
            Log::error('FARESHUNT_STRIPE_BOOKING_EMAIL_FAILED', ['booking_id' => $bookingId, 'error' => get_class($e)]);
        }
        try {
            $flight->DeleteTemp($snap['contact']['email'], $snap['contact']['mobile']);
        } catch (\Throwable $e) {
            // temp enquiry cleanup is optional
        }

        return $mismatch ? 'manual_review:aerobook_price_mismatch' : 'booked:'.$bookingId;
    }

    private function buildBooking(object $attempt, array $snap, array $result, object $flight): Booking
    {
        $pax = $snap['passengers'];
        $sectors = $flight->filterSectors($result['Offers']['OfferInfo'] ?? null);
        $info = $snap['price']['info_markup'] ?? null;
        $card = $this->cardDetails((string) $attempt->stripe_payment_intent_id);

        $b = new Booking();
        $b->i_field_encode = (string) ($snap['i_field_encode'] ?? '');
        $b->o_Name = ($pax[0]['Name'] ?? '').' '.($pax[0]['MiddleName'] ?? '').' '.($pax[0]['Surname'] ?? '');
        $b->o_Email = $snap['contact']['email'];
        $b->o_Phone = $snap['contact']['mobile_telcode'].' '.$snap['contact']['mobile'];
        $b->o_Passenger = $flight->filterPassengers($result['PaxList'], $pax);
        $b->o_SectorGo = $sectors['Rph_1'] ?? null;
        $b->o_SectorBack = $sectors['Rph_2'] ?? null;
        if (Schema::hasColumn('bookings', 'o_Itinerary')) {
            $b->o_Itinerary = $sectors['Itinerary'] ?? null;
        }
        $b->o_Price = $snap['price']['service_p'];
        $b->o_FullPrice = $snap['price']['full_p'];
        $b->o_Credit = json_encode([
            'payment_method' => app(StripeTestPaymentService::class)->mode() === 'live' ? 'stripe' : 'stripe_test',
            'card_brand' => $card['brand'],
            'card_last4' => $card['last4'],
            'stripe_ref' => (string) $attempt->stripe_payment_intent_id,
            'attempt_id' => (int) $attempt->id,
        ]);
        $b->o_Billing = json_encode($snap['billing']);
        // FareLeaders snapshots carry null: legacy FareLeaders bookings never set is_markup.
        if (($snap['price']['is_markup'] ?? null) !== null) {
            $b->is_markup = $snap['price']['is_markup'];
        }
        $b->o_Other = $flight->filterOther(
            ['code' => $snap['search_code'], 'OfferCode' => $snap['offer_code']],
            $result,
            is_array($info) ? json_encode($info) : (string) $info
        );
        $b->source = $snap['source'];
        $b->confirmation_token = bin2hex(random_bytes(32));

        return $b;
    }

    /** Brand + last 4 from Stripe (never the card number). Empty when Stripe does not answer. */
    private function cardDetails(string $piId): array
    {
        try {
            $pi = $this->stripe()->paymentIntents->retrieve($piId, ['expand' => ['latest_charge']]);
            $arr = is_object($pi) && method_exists($pi, 'toArray') ? $pi->toArray() : (array) $pi;
            $card = $arr['latest_charge']['payment_method_details']['card'] ?? [];
            $brands = ['visa' => 'Visa', 'mastercard' => 'Mastercard', 'amex' => 'American Express', 'discover' => 'Discover',
                'diners' => 'Diners Club', 'jcb' => 'JCB', 'unionpay' => 'UnionPay'];
            $brand = strtolower((string) ($card['brand'] ?? ''));
            $last4 = (string) ($card['last4'] ?? '');

            return [
                'brand' => $brands[$brand] ?? ($brand === '' ? '' : 'Card'),
                'last4' => preg_match('/^\d{4}$/D', $last4) ? $last4 : '',
            ];
        } catch (\Throwable $e) {
            Log::warning('FARESHUNT_STRIPE_CARD_DETAILS_UNAVAILABLE', ['error' => get_class($e)]);
            return ['brand' => '', 'last4' => ''];
        }
    }

    private function releaseHold(object $attempt, array $snap, string $reason): string
    {
        try {
            $this->stripe()->paymentIntents->cancel((string) $attempt->stripe_payment_intent_id, ['cancellation_reason' => 'abandoned']);
        } catch (\Throwable $e) {
            return $this->review((int) $attempt->id, 'hold_release_failed', ['error' => get_class($e), 'why' => $reason]);
        }

        app(StripeTestAttemptStateService::class)->transition((int) $attempt->id, 'authorized', 'cancelled', [
            'cancelled_at' => now(),
            'booking_status' => $reason,
            'last_error_code' => $reason,
            'checkout_identity_hash' => null,
        ]);
        Log::warning('FARESHUNT_STRIPE_BOOKING_HOLD_RELEASED', ['attempt_id' => (int) $attempt->id, 'reason' => $reason]);

        $this->notifyReleased($attempt, (string) ($snap['contact']['email'] ?? ''), $reason);

        return 'released:'.$reason;
    }

    private function notifyReleased(object $attempt, string $customer, string $reason): void
    {
        if (!$this->mailOn()) {
            return;
        }
        $why = $reason === 'fare_changed' ? 'the airline fare changed' : 'the fare is no longer available';
        try {
            if (filter_var($customer, FILTER_VALIDATE_EMAIL)) {
                $body = "Dear customer,\n\nWe could not confirm your flight because {$why} while we were booking it.\n"
                    ."The hold on your card has been released, so no money has been taken. Depending on your bank, "
                    ."the hold can take a few days to disappear from your statement.\n\n"
                    ."Please search again on fareshunt.com or reply to this email and our team will help you.\n\nFares Hunt";
                Mail::raw($body, fn ($m) => $m->to($customer)->subject('Your Fares Hunt booking could not be confirmed'));
            }
            $team = app(StripeAlertService::class)->recipients();
            if ($team) {
                $body = 'Stripe '.strtoupper(app(StripeTestPaymentService::class)->mode()).' attempt #'.$attempt->id.': booking NOT made because '.$why.".\n"
                    .'The card hold was released automatically and the customer was emailed. Amount: USD '
                    .number_format(((int) $attempt->amount_cents) / 100, 2)."\nPanel: ".StripeAlertService::PANEL_URL;
                Mail::raw($body, fn ($m) => $m->to($team)->subject('[FaresHunt Stripe '.strtoupper(app(StripeTestPaymentService::class)->mode()).'] Booking not made - hold released (#'.$attempt->id.')'));
            }
        } catch (\Throwable $e) {
            Log::error('FARESHUNT_STRIPE_RELEASE_EMAIL_FAILED', ['attempt_id' => (int) $attempt->id, 'error' => get_class($e)]);
        }
    }

    /** D7: unclear outcome -> human. Hold is kept; alert-check mails the team. */
    private function review(int $attemptId, string $code, array $ctx = []): string
    {
        DB::table('stripe_payment_attempts')->where('id', $attemptId)->where('booking_status', 'booking')
            ->update(['booking_status' => 'manual_review', 'last_error_code' => mb_substr($code, 0, 100), 'updated_at' => now()]);
        Log::error('FARESHUNT_STRIPE_BOOKING_MANUAL_REVIEW', ['attempt_id' => $attemptId, 'reason' => $code] + $ctx);

        return 'manual_review:'.$code;
    }

    private function mailOn(): bool
    {
        try {
            return strtoupper(trim((string) DB::table('settings')->where('name', 'is_mail')->orderByDesc('id')->value('value'))) === 'ON';
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function cents($value): ?int
    {
        if (!is_numeric($value) || !is_finite((float) $value)) {
            return null;
        }

        return (int) round((float) $value * 100);
    }

    /* ---- overridable in tests (no supplier / Stripe call) ---- */

    protected function flight(): object
    {
        return app(\App\Http\Controllers\FlightController::class);
    }

    protected function stripe(): object
    {
        return app(StripeTestPaymentService::class)->client();
    }

    protected function supplierCents(array $prebook, array $fields): int
    {
        return (int) app(StripeTestSupplierFareService::class)->validate($prebook, $fields)['supplier_amount_cents'];
    }

    protected function saveBooking(Booking $b): int
    {
        $b->save();

        return (int) $b->id;
    }
}
