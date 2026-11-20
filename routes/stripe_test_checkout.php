<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StripeTestCheckoutController;
use App\Http\Controllers\StripeTestReturnController;

Route::post('/stripe-test/checkout', [
    StripeTestCheckoutController::class, 'create'
])->middleware('throttle:10,1,stripe.test.checkout')->name('stripe.test.checkout');

// Step D: customer return pages. Display only - they never mark anything paid.
Route::get('/stripe-test/return/success/{reference}', [
    StripeTestReturnController::class, 'success'
])->where('reference', '[a-f0-9]{64}')->middleware('throttle:30,1,stripe.test.return.success')->name('stripe.test.return.success');

// Step F5: live booking status for the return page (read-only JSON).
Route::get('/stripe-test/return/status/{reference}', [
    StripeTestReturnController::class, 'status'
])->where('reference', '[a-f0-9]{64}')->middleware('throttle:60,1,stripe.test.return.status')->name('stripe.test.return.status');

Route::get('/stripe-test/return/cancel/{reference}', [
    StripeTestReturnController::class, 'cancel'
])->where('reference', '[a-f0-9]{64}')->middleware('throttle:30,1,stripe.test.return.cancel')->name('stripe.test.return.cancel');

// Step F2b: tester gate. Valid secret token -> 7-day tester cookie on this browser only.
Route::get('/stripe-test/tester/{token}', [
    \App\Http\Controllers\StripeTestTesterController::class, 'on'
])->where('token', '[A-Za-z0-9]{32,128}')->middleware('throttle:5,1,stripe.test.tester.on')->name('stripe.test.tester.on');

Route::get('/stripe-test/tester-off', [
    \App\Http\Controllers\StripeTestTesterController::class, 'off'
])->middleware('throttle:10,1,stripe.test.tester.off')->name('stripe.test.tester.off');

// Step F3: website booking form -> Stripe TEST checkout (tester-gated). No AeroBook here.
Route::post('/stripe-test/booking-start', [
    \App\Http\Controllers\StripeTestBookingStartController::class, 'start'
])->middleware('throttle:10,1,stripe.test.booking.start')->name('stripe.test.booking.start');

// Step G: CRM -> Payment Gateway (Stripe) panel. crm.fareshunt.com only.
// CrmPermissionGate maps these names; the controller also requires Super Admin.
Route::domain('crm.fareshunt.com')->get('/payment-gateway', [
    \App\Http\Controllers\CrmStripeGatewayController::class, 'index'
])->name('crm.stripe_gateway');

Route::domain('crm.fareshunt.com')->post('/payment-gateway', [
    \App\Http\Controllers\CrmStripeGatewayController::class, 'update'
])->middleware('throttle:20,1,crm.stripe_gateway_update')->name('crm.stripe_gateway_update');
