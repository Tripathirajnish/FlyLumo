<?php

namespace App\Http\Controllers;

use App\Services\StripeBookingExecutorService;
use App\Services\StripeTestWebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StripeTestWebhookController extends Controller
{
    public function handle(Request $request): JsonResponse
    {
        [$status, $result] = app(StripeTestWebhookService::class)->handle(
            (string) $request->getContent(),
            $request->header('Stripe-Signature')
        );

        // Step F3b: money is on hold -> book AFTER Stripe gets its 200 (cron is the backup).
        if ($status === 200 && $result === 'authorized') {
            app()->terminating(function () {
                try {
                    @set_time_limit(300);
                    app(StripeBookingExecutorService::class)->runDue(3);
                } catch (\Throwable $e) {
                    Log::error('FARESHUNT_STRIPE_EXECUTOR_AFTER_WEBHOOK_FAILED', ['error' => get_class($e)]);
                }
            });
        }

        return response()->json(['received' => $status === 200, 'result' => $result], $status);
    }
}
