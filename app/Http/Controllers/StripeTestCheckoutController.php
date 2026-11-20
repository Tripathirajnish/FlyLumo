<?php

namespace App\Http\Controllers;

use App\Services\StripeTestPaymentService;
use App\Services\StripeTestFareService;
use App\Services\StripeTestSupplierQuoteService;
use App\Services\StripePaymentModeService;
use App\Services\StripeTestCheckoutService;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class StripeTestCheckoutController extends Controller
{
    public function create(
        Request $request,
        StripeTestPaymentService $payments
    ): JsonResponse {
        // Single decision point: .env ceiling + CRM switch + flow readiness.
        // Anything else (or any error) = legacy flow, Stripe unavailable.
        if (!app(StripePaymentModeService::class)->isStripeEnabledForCustomer(StripePaymentModeService::FLOW_WEBSITE)) {
            return response()->json([
                'success' => false,
                'message' => 'Stripe test checkout is unavailable.',
            ], 503);
        }

        $validated = $request->validate([
            'code' => ['required', 'string', 'regex:/^[a-zA-Z0-9_-]{1,128}$/D'],
            // Owner decision (3 Nov 2026): Stripe is website-only for now.
            'flow' => ['required', 'in:website'],
            // Same hidden "service" field the legacy booking form posts
            // (add-on Yes/No choices only; prices are recalculated server-side).
            'service' => ['nullable', 'string', 'max:8000'],
        ]);

        try {
            $choices = [];
            $service = $validated['service'] ?? null;

            if (is_string($service) && trim($service) !== '') {
                $choices = json_decode($service, true, 4, JSON_THROW_ON_ERROR);

                if (!is_array($choices)) {
                    throw new \RuntimeException('Invalid Stripe add-on payload.');
                }
            }

            $fare = app(StripeTestFareService::class)->calculate(
                $validated['code'],
                $validated['flow'],
                $choices
            );

            // Fresh supplier quote (AeroPrebook, no booking) must match.
            $quote = app(StripeTestSupplierQuoteService::class)->verify(
                $validated['code'],
                $fare
            );

            // Step D: attempt + Stripe TEST Checkout Session (manual capture).
            $checkout = app(StripeTestCheckoutService::class)->start(
                (string) $request->session()->getId(),
                $validated['code'],
                $fare,
                $quote
            );
        } catch (\RuntimeException $e) {
            if ($e->getCode() === StripeTestSupplierQuoteService::FARE_CHANGED) {
                return response()->json([
                    'success' => false,
                    'message' => 'This fare has changed or is no longer available. Please search again.',
                ], 409);
            }

            return response()->json([
                'success' => false,
                'message' => 'Stripe checkout fare verification failed.',
            ], 422);
        } catch (\Throwable $e) {
            // e.g. Stripe API error. Log the class/code only - never the payload.
            Log::warning('FARESHUNT_STRIPE_TEST_CHECKOUT_ERROR', [
                'error' => get_class($e),
                'code' => method_exists($e, 'getStripeCode') ? $e->getStripeCode() : null,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Stripe checkout fare verification failed.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'url' => $checkout['url'],
        ], 200);
    }
}
