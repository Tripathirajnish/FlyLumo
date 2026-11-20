<?php

use Illuminate\Support\Facades\Route;





Route::get('/authorization/{token}',[App\Http\Controllers\AuthorizationController::class,'publicForm'])->name('authorization.public');
Route::post('/authorization/{token}',[App\Http\Controllers\AuthorizationController::class,'submit'])->name('authorization.submit');


// Legacy /crm compatibility routes
Route::get('/crm/website-analytics',[App\Http\Controllers\WebsiteAnalyticsController::class,'index'])->name('legacy.crm.website_analytics');
Route::get('/crm/google-analytics',[App\Http\Controllers\GoogleAnalyticsController::class,'index'])->name('legacy.crm.google_analytics');
Route::get('/crm/bookings/{id}/authorization',[App\Http\Controllers\AuthorizationController::class,'booking'])->whereNumber('id')->name('legacy.crm.authorization_center');
Route::post('/crm/bookings/{id}/authorization',[App\Http\Controllers\AuthorizationController::class,'create'])->whereNumber('id')->name('legacy.crm.authorization_create');
Route::post('/crm/authorizations/{id}/resend',[App\Http\Controllers\AuthorizationController::class,'resend'])->whereNumber('id')->name('legacy.crm.authorization_resend');
Route::post('/crm/authorizations/{id}/revoke',[App\Http\Controllers\AuthorizationController::class,'revoke'])->whereNumber('id')->name('legacy.crm.authorization_revoke');
Route::get('/crm/authorizations/{id}/evidence',[App\Http\Controllers\AuthorizationController::class,'evidence'])->whereNumber('id')->name('legacy.crm.authorization_evidence');
Route::get('/crm/authorization-documents/{id}',[App\Http\Controllers\AuthorizationController::class,'document'])->whereNumber('id')->name('legacy.crm.authorization_document');

// Canonical clean CRM subdomain routes
Route::domain('crm.fareshunt.com')->get('/website-analytics',[App\Http\Controllers\WebsiteAnalyticsController::class,'index'])->name('crm.website_analytics');
Route::domain('crm.fareshunt.com')->get('/google-analytics',[App\Http\Controllers\GoogleAnalyticsController::class,'index'])->name('crm.google_analytics');
Route::domain('crm.fareshunt.com')->get('/bookings/{id}/authorization',[App\Http\Controllers\AuthorizationController::class,'booking'])->whereNumber('id')->name('crm.authorization_center');
Route::domain('crm.fareshunt.com')->post('/bookings/{id}/authorization',[App\Http\Controllers\AuthorizationController::class,'create'])->whereNumber('id')->name('crm.authorization_create');
Route::domain('crm.fareshunt.com')->post('/authorizations/{id}/resend',[App\Http\Controllers\AuthorizationController::class,'resend'])->whereNumber('id')->name('crm.authorization_resend');
Route::domain('crm.fareshunt.com')->post('/authorizations/{id}/revoke',[App\Http\Controllers\AuthorizationController::class,'revoke'])->whereNumber('id')->name('crm.authorization_revoke');
Route::domain('crm.fareshunt.com')->get('/authorizations/{id}/evidence',[App\Http\Controllers\AuthorizationController::class,'evidence'])->whereNumber('id')->name('crm.authorization_evidence');
Route::domain('crm.fareshunt.com')->get('/authorization-documents/{id}',[App\Http\Controllers\AuthorizationController::class,'document'])->whereNumber('id')->name('crm.authorization_document');
