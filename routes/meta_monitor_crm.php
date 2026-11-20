<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MetaMonitorController;

// Legacy /crm compatibility route
Route::get('/crm/meta-monitor', [MetaMonitorController::class, 'index'])
    ->name('legacy.crm.meta_monitor');

// Canonical clean CRM subdomain route
Route::domain('crm.fareshunt.com')
    ->get('/meta-monitor', [MetaMonitorController::class, 'index'])
    ->name('crm.meta_monitor');
