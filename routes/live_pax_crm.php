<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LivePaxMonitorController;


// Legacy /crm compatibility routes


Route::get('/crm/live-pax', [LivePaxMonitorController::class, 'index'])->name('legacy.crm.live_pax');
Route::get('/crm/live-pax/data', [LivePaxMonitorController::class, 'data'])->name('legacy.crm.live_pax_data');
Route::get('/crm/live-pax/session/{id}', [LivePaxMonitorController::class, 'session'])->whereNumber('id')->name('legacy.crm.live_pax_session');
Route::get('/crm/live-pax/session/{id}/chunks', [LivePaxMonitorController::class, 'chunks'])->whereNumber('id')->name('legacy.crm.live_pax_chunks');
Route::get('/crm/live-pax/session/{id}/replay', [LivePaxMonitorController::class, 'replay'])->whereNumber('id')->name('legacy.crm.live_pax_replay');
Route::get('/crm/live-pax/heatmap/data', [LivePaxMonitorController::class, 'heatmap'])->name('legacy.crm.live_pax_heatmap');


// Canonical clean CRM subdomain routes


Route::domain('crm.fareshunt.com')->get('/live-pax', [LivePaxMonitorController::class, 'index'])->name('crm.live_pax');
Route::domain('crm.fareshunt.com')->get('/live-pax/data', [LivePaxMonitorController::class, 'data'])->name('crm.live_pax_data');
Route::domain('crm.fareshunt.com')->get('/live-pax/session/{id}', [LivePaxMonitorController::class, 'session'])->whereNumber('id')->name('crm.live_pax_session');
Route::domain('crm.fareshunt.com')->get('/live-pax/session/{id}/chunks', [LivePaxMonitorController::class, 'chunks'])->whereNumber('id')->name('crm.live_pax_chunks');
Route::domain('crm.fareshunt.com')->get('/live-pax/session/{id}/replay', [LivePaxMonitorController::class, 'replay'])->whereNumber('id')->name('crm.live_pax_replay');
Route::domain('crm.fareshunt.com')->get('/live-pax/heatmap/data', [LivePaxMonitorController::class, 'heatmap'])->name('crm.live_pax_heatmap');
