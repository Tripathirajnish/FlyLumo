<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StripeTestWebhookController;

// Stripe -> server webhook (Step E). Registered OUTSIDE the "web" group:
// no session, no CSRF (Stripe cannot send a CSRF token). Authenticity is
// proven by the Stripe-Signature header instead.
Route::post('/stripe-test/webhook', [
    StripeTestWebhookController::class, 'handle'
])->name('stripe.test.webhook');
