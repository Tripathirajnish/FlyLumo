<?php

namespace App\Http\Controllers;

use App\Services\StripeBookingFormService;
use App\Services\StripeBookingSnapshotService;
use App\Services\StripePaymentModeService as Mode;
use App\Services\StripeTestCheckoutService;
use App\Services\StripeTestFareService;
use App\Services\StripeTestSupplierQuoteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use RuntimeException;

/**
 * Step F3: website booking form -> Stripe TEST Checkout (tester-gated, F2b).
 * Validates the form (no card fields), checks fare + fresh supplier quote,
 * creates/reuses the attempt, stores the encrypted snapshot, then sends the
 * customer to Stripe. NO supplier booking happens here: AeroBook runs only
 * after the signature-verified webhook (F3b). Legacy submitBooking() untouched.
 */
class StripeTestBookingStartController extends Controller
{
    public const MSG_GENERIC = 'We could not start the secure payment. No money has been taken. Please try again.';
    public const MSG_FARE = 'This fare has changed or is no longer available. No money has been taken. Please search again.';
    public const MSG_BUSY = 'Your payment is already being processed. Please do not pay again.';

    public function start(Request $request)
    {
        // Step FL: same controller for both flows; the hidden "flow" field picks one.
        $flow = (string) $request->input('flow', Mode::FLOW_WEBSITE);
        if (!in_array($flow, [Mode::FLOW_WEBSITE, Mode::FLOW_FARELEADERS], true)) {
            abort(404);
        }

        if (!app(Mode::class)->isStripeEnabledForCustomer($flow)) {
            abort(404);
        }

        $code = (string) $request->input('code', '');
        $key = (string) $request->input('key', '');
        if (!preg_match('/^[a-zA-Z0-9_-]{1,128}$/D', $code) || !preg_match('/^[a-zA-Z0-9_-]{1,64}$/D', $key)) {
            abort(404);
        }

        $form = app(StripeBookingFormService::class);
        $ctx = $form->sessionContext($code, $flow);
        if ($ctx === null) {
            return redirect()->route('not-found-page');
        }

        $back = $flow === Mode::FLOW_FARELEADERS
            ? $this->fareleadersBack($request)
            : url('flight-payment/'.$code.'/'.$key);
        $safeInput = $request->except(StripeBookingFormService::CARD_FIELDS);

        $validator = Validator::make($request->all(), $form->rules($request, $flow));
        if ($validator->fails()) {
            return redirect($back)->withErrors($validator)->withInput($safeInput);
        }

        try {
            $choices = $form->choices($request->input('service'));
            $fare = app(StripeTestFareService::class)->calculate($code, $flow, $choices);
            $snapshot = $form->buildSnapshot($request, $ctx, $fare, $choices);
            $quote = app(StripeTestSupplierQuoteService::class)->verify($code, $fare);

            // FareLeaders without a page-open supplier price, or (owner decision 17 Nov 2026)
            // a supplier price that changed since the search: the fresh quote is the price
            // the executor re-checks right before AeroBook. The customer amount is unchanged.
            if (($snapshot['price']['supplier_expected_cents'] ?? null) !== (int) $quote['supplier_amount_cents']) {
                $fare['supplier_expected_cents'] = (int) $quote['supplier_amount_cents'];
                $snapshot['price']['supplier_expected_cents'] = $fare['supplier_expected_cents'];
            }
            $checkout = app(StripeTestCheckoutService::class)
                ->start((string) $request->session()->getId(), $code, $fare, $quote);

            $attemptId = (int) ($checkout['attempt_id'] ?? 0);
            $status = DB::table('stripe_payment_attempts')->where('id', $attemptId)->value('status');

            // Never overwrite the snapshot of an attempt that is already paid/closed.
            if ($status !== 'pending' || !is_string($checkout['url'] ?? null)) {
                throw new RuntimeException('Stripe attempt is not pending.');
            }

            app(StripeBookingSnapshotService::class)->store($attemptId, $snapshot);
            session([($flow === Mode::FLOW_FARELEADERS ? 'Fareleader' : $code).'.Booking-Form' => $form->sessionForm($request)]);
            // 19 Nov 2026: if the customer cancels on Stripe, the cancel page sends them back here
            // (details kept) instead of leaving them on a page whose only link is the homepage.
            session(['FH-Stripe-Back' => ['url' => $back, 'attempt_id' => $attemptId, 'at' => time()]]);
        } catch (RuntimeException $e) {
            $msg = self::MSG_GENERIC;
            if ($e->getCode() === StripeTestSupplierQuoteService::FARE_CHANGED) {
                $msg = self::MSG_FARE;
            } elseif ($e->getMessage() === 'This payment is already being processed.') {
                $msg = self::MSG_BUSY;
            }
            Log::warning('FARESHUNT_STRIPE_BOOKING_START_FAILED', [
                'reason' => mb_substr($e->getMessage(), 0, 150),
                'code' => $e->getCode(),
            ]);

            return $this->fail($back, $safeInput, $msg);
        } catch (\Throwable $e) {
            Log::warning('FARESHUNT_STRIPE_BOOKING_START_FAILED', ['error' => get_class($e)]);

            return $this->fail($back, $safeInput, self::MSG_GENERIC);
        }

        Log::info('FARESHUNT_STRIPE_BOOKING_START', [
            'attempt_id' => $attemptId,
            'reused' => (bool) ($checkout['reused'] ?? false),
        ]);

        return redirect()->away($checkout['url']);
    }

    /** Back to the FareLeaders booking page (same link legacy submitBookingFLeader() uses). */
    private function fareleadersBack(Request $request): string
    {
        $q = [];
        foreach (['priceinfo' => 'priceinfo', 'field' => 'field', 'searchguid' => 'SearchGuid', 'offercode' => 'OfferCode'] as $k => $in) {
            $v = $request->input($in);
            if (!is_string($v) || $v === '' || strlen($v) > 5000) {
                return url('/');
            }
            $q[$k] = $v;
        }

        return url('flights-payment').'?'.http_build_query($q);
    }

    private function fail(string $back, array $safeInput, string $msg)
    {
        session(['Booking-Error-Msz' => $msg]);

        return redirect($back)->withInput($safeInput);
    }
}
