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
      //Oauth user
      Route::get('/me', [App\Http\Controllers\API\AuthAPIController::class, 'me'])->name('auth.me');
      Route::delete('/oauth/logout', [App\Http\Controllers\API\AuthAPIController::class, 'logout'])->name('auth.logout');

      //Gift cards resource
      Route::post('/gift-cards/users/{user}', [App\Http\Controllers\API\GiftCardAPIController::class, 'store'])->middleware('idempotency')->name('gift-cards.store');
      Route::post('/gift-cards/', [App\Http\Controllers\API\GiftCardAPIController::class, 'storeAuth'])->middleware('idempotency')->name('gift-cards.store.me');
      Route::get('/gift-cards/users/{user}', [App\Http\Controllers\API\GiftCardAPIController::class, 'index'])->name('gift-cards.index');
      Route::get('/gift-cards/', [App\Http\Controllers\API\GiftCardAPIController::class, 'indexAuth'])->name('gift-cards.index.me');
      Route::resource('gift-cards', App\Http\Controllers\API\GiftCardAPIController::class)
        ->except(['create', 'store', 'index', 'edit']);

      //Qr sessions resource
      Route::resource('qr-sessions', App\Http\Controllers\API\QRSessionAPIController::class)
        ->except(['create', 'update', 'edit']);
      Route::patch('qr-sessions/{qrSession}', [App\Http\Controllers\API\QRSessionAPIController::class, 'verify'])->name('qr-sessions.verify');

      //User actions
      Route::get('/users/', [App\Http\Controllers\API\UserAPIController::class, 'index'])->name('users.index');
      Route::post('/file/upload', [App\Http\Controllers\API\FileAPIController::class, 'upload'])->name('files.upload');

      //Categories resource
      Route::resource('categories', App\Http\Controllers\API\CategoryAPIController::class)
          ->except(['create', 'edit']);

});

//Route::resource('beneficiaries', App\Http\Controllers\API\BeneficiaryAPIController::class)
//    ->except(['create', 'edit']);

//Route::resource('designs', App\Http\Controllers\API\DesignAPIController::class)
//    ->except(['create', 'edit']);


Route::resource('partner-categories', App\Http\Controllers\API\UserCategoryAPIController::class)
    ->except(['create', 'edit']);
