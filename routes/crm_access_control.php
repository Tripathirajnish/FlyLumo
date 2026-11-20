<?php

use Illuminate\Support\Facades\Route;


// Legacy /crm compatibility routes

Route::get('/crm/settings/team-access',[App\Http\Controllers\TeamAccessController::class,'index'])->name('legacy.crm.team_access');

Route::post('/crm/settings/users',[App\Http\Controllers\TeamAccessController::class,'storeUser'])->name('legacy.crm.user_store');
Route::post('/crm/settings/users/{id}',[App\Http\Controllers\TeamAccessController::class,'updateUser'])->whereNumber('id')->name('legacy.crm.user_update');
Route::post('/crm/settings/users/{id}/toggle',[App\Http\Controllers\TeamAccessController::class,'toggleUser'])->whereNumber('id')->name('legacy.crm.user_toggle');
Route::delete('/crm/settings/users/{id}',[App\Http\Controllers\TeamAccessController::class,'deleteUser'])->whereNumber('id')->name('legacy.crm.user_delete');
Route::post('/crm/settings/users/{id}/permissions',[App\Http\Controllers\TeamAccessController::class,'saveUserPermissions'])->whereNumber('id')->name('legacy.crm.user_permissions');
Route::post('/crm/settings/users/{id}/permissions/reset',[App\Http\Controllers\TeamAccessController::class,'resetUserPermissions'])->whereNumber('id')->name('legacy.crm.user_permissions_reset');

Route::post('/crm/settings/roles',[App\Http\Controllers\TeamAccessController::class,'storeRole'])->name('legacy.crm.role_store');
Route::post('/crm/settings/roles/{id}',[App\Http\Controllers\TeamAccessController::class,'updateRole'])->whereNumber('id')->name('legacy.crm.role_update');
Route::delete('/crm/settings/roles/{id}',[App\Http\Controllers\TeamAccessController::class,'deleteRole'])->whereNumber('id')->name('legacy.crm.role_delete');


// Canonical clean CRM subdomain routes

Route::domain('crm.fareshunt.com')->get('/settings/team-access',[App\Http\Controllers\TeamAccessController::class,'index'])->name('crm.team_access');

Route::domain('crm.fareshunt.com')->post('/settings/users',[App\Http\Controllers\TeamAccessController::class,'storeUser'])->name('crm.user_store');
Route::domain('crm.fareshunt.com')->post('/settings/users/{id}',[App\Http\Controllers\TeamAccessController::class,'updateUser'])->whereNumber('id')->name('crm.user_update');
Route::domain('crm.fareshunt.com')->post('/settings/users/{id}/toggle',[App\Http\Controllers\TeamAccessController::class,'toggleUser'])->whereNumber('id')->name('crm.user_toggle');
Route::domain('crm.fareshunt.com')->delete('/settings/users/{id}',[App\Http\Controllers\TeamAccessController::class,'deleteUser'])->whereNumber('id')->name('crm.user_delete');
Route::domain('crm.fareshunt.com')->post('/settings/users/{id}/permissions',[App\Http\Controllers\TeamAccessController::class,'saveUserPermissions'])->whereNumber('id')->name('crm.user_permissions');
Route::domain('crm.fareshunt.com')->post('/settings/users/{id}/permissions/reset',[App\Http\Controllers\TeamAccessController::class,'resetUserPermissions'])->whereNumber('id')->name('crm.user_permissions_reset');

Route::domain('crm.fareshunt.com')->post('/settings/roles',[App\Http\Controllers\TeamAccessController::class,'storeRole'])->name('crm.role_store');
Route::domain('crm.fareshunt.com')->post('/settings/roles/{id}',[App\Http\Controllers\TeamAccessController::class,'updateRole'])->whereNumber('id')->name('crm.role_update');
Route::domain('crm.fareshunt.com')->delete('/settings/roles/{id}',[App\Http\Controllers\TeamAccessController::class,'deleteRole'])->whereNumber('id')->name('crm.role_delete');
