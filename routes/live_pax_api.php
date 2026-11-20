<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LivePaxTrackingController;

Route::middleware('throttle:600,1,live-pax')->prefix('live-pax')->group(function () {
    Route::post('/heartbeat', [LivePaxTrackingController::class, 'heartbeat']);
    Route::post('/chunk', [LivePaxTrackingController::class, 'chunk']);
});
