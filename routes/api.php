<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// use App\Http\Controllers\ApiController;

// // Fare Leader API
// Route::GET('/search', [ApiController::class, 'getFlights']);
// Route::POST('/callapi', [ApiController::class, 'testSearchApi']);   # For Testing

// Live Pax Monitor - isolated public telemetry endpoints.
require __DIR__.'/live_pax_api.php';
