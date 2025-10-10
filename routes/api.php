<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


Route::group(['prefix' => 'auth'], function () {
    Route::post('/register', [App\Http\Controllers\API\AuthAPIController::class, 'register'])->name('auth.register');
    Route::post('/otp/request/{phone}', [App\Http\Controllers\API\AuthAPIController::class, 'otp_request'])->name('auth.otp.request');
    Route::put('/otp/verify/{phone}', [App\Http\Controllers\API\AuthAPIController::class, 'otp_verify'])->middleware('throttle:20,1')->name('auth.otp.verify');
});


Route::group(['middleware' => ['auth:api']], function () {
      Route::get('/me', [App\Http\Controllers\API\AuthAPIController::class, 'me'])->name('auth.me');
      Route::post('/logout', [App\Http\Controllers\API\AuthAPIController::class, 'logout'])->name('auth.logout');
});

Route::post('gift-cards/users/{user}', [App\Http\Controllers\API\GiftCardAPIController::class, 'store'])->middleware('idempotency')->name('gift-cards.store');
Route::resource('gift-cards', App\Http\Controllers\API\GiftCardAPIController::class)
    ->except(['create', 'store', 'edit']);

Route::resource('qr-sessions', App\Http\Controllers\API\QRSessionAPIController::class)
    ->except(['create', 'edit']);

Route::resource('beneficiaries', App\Http\Controllers\API\BeneficiaryAPIController::class)
    ->except(['create', 'edit']);

Route::resource('designs', App\Http\Controllers\API\DesignAPIController::class)
    ->except(['create', 'edit']);

Route::resource('card-events', App\Http\Controllers\API\CardEventAPIController::class)
    ->except(['create', 'edit']);
