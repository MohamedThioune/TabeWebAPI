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

      //Customer scope
      Route::group(['middleware' => ['role:customer|admin']], function () {
          //Gift cards
          Route::post('/gift-cards', [App\Http\Controllers\API\GiftCardAPIController::class, 'storeAuth'])->middleware('idempotency')->name('gift-cards.store.me');
          Route::get('/gift-cards', [App\Http\Controllers\API\GiftCardAPIController::class, 'indexAuth'])->name('gift-cards.index.me');

          //Qr sessions
          Route::post('qr-sessions', [App\Http\Controllers\API\QRSessionAPIController::class, 'store'])->name('qr-sessions.store');
          Route::patch('qr-sessions/{qrSession}', [App\Http\Controllers\API\QRSessionAPIController::class, 'verify'])->name('qr-sessions.verify');
          Route::get('qr-sessions/{qrSession}', [App\Http\Controllers\API\QRSessionAPIController::class, 'show'])->name('qr-sessions.show');
      });

      //Admin scope
      Route::group(['middleware' => ['role:admin']], function () {
          Route::resource('qr-sessions', App\Http\Controllers\API\QRSessionAPIController::class)
              ->except(['create', 'edit', 'store', 'update', 'show']);

          Route::get('/gift-cards/users/{user}', [App\Http\Controllers\API\GiftCardAPIController::class, 'index'])->name('gift-cards.index'); //List any gift cards via user id
          Route::post('/gift-cards/users/{user}', [App\Http\Controllers\API\GiftCardAPIController::class, 'store'])->middleware('idempotency')->name('gift-cards.store'); //Store any gift cards via user id
          Route::resource('gift-cards', App\Http\Controllers\API\GiftCardAPIController::class)
              ->except(['create', 'edit', 'store', 'index']);

          // Route::get('/users', [App\Http\Controllers\API\UserAPIController::class, 'index'])->name('users.index');
          // Route::resource('categories', App\Http\Controllers\API\CategoryAPIController::class)
          //    ->except(['create', 'edit']);
      });

      //User actions (list users, update user, upload file)
      Route::get('/users', [App\Http\Controllers\API\UserAPIController::class, 'index'])->name('users.index');
      Route::patch('/users/{user}', [App\Http\Controllers\API\UserAPIController::class, 'update'])->name('users.update');
      Route::post('/file/upload', [App\Http\Controllers\API\FileAPIController::class, 'upload'])->name('files.upload');

      //Categories resource
      Route::resource('categories', App\Http\Controllers\API\CategoryAPIController::class)
          ->except(['create', 'edit']);
});
