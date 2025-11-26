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
// PayDunya IPN
Route::post('/paydunya/ipn', [\App\Http\Controllers\PaydunyaController::class, 'ipn_handle'])->name('paydunya.ipn');

Route::group(['prefix' => 'auth'], function () {
    Route::post('/register', [App\Http\Controllers\API\AuthAPIController::class, 'register'])->name('auth.register');
    Route::post('/otp/request/{phone}', [App\Http\Controllers\API\AuthAPIController::class, 'otp_request'])->name('auth.otp.request');
    Route::put('/otp/verify/{phone}', [App\Http\Controllers\API\AuthAPIController::class, 'otp_verify'])->middleware('throttle:20,1')->name('auth.otp.verify');
    Route::patch('/reset/password/{phone}', [App\Http\Controllers\API\AuthAPIController::class, 'reset_password'])->name('auth.reset_password');
});

Route::group(['middleware' => ['auth:api']], function () {
      // Oauth user
      Route::get('/me', [App\Http\Controllers\API\AuthAPIController::class, 'me'])->name('auth.me');
      Route::delete('/oauth/logout', [App\Http\Controllers\API\AuthAPIController::class, 'logout'])->name('auth.logout');
      Route::delete('/me', [App\Http\Controllers\API\UserAPIController::class, 'destroy'])->name('auth.delete'); // delete all data relatives to the connected user !!
      Route::patch('/update/password', [App\Http\Controllers\API\UserAPIController::class, 'update_password'])->name('auth.modify_password');

      // User actions (list partner, update user, upload file, notifications)
      Route::get('/partners', [App\Http\Controllers\API\UserAPIController::class, 'indexPartner'])->name('users.index.partner');
      Route::patch('/users', [App\Http\Controllers\API\UserAPIController::class, 'updateAuth'])->name('users.update.me');
      Route::post('/file/upload', [App\Http\Controllers\API\FileAPIController::class, 'upload'])->name('files.upload');

      // Notifications (get notifications, read notification, read all notifications, delete notification)
      Route::get('/notifications/me', [App\Http\Controllers\API\NotificationAPIController::class, 'indexAuth'])->name('notifications.me');
      Route::patch('/notifications/me/{notification}', [App\Http\Controllers\API\NotificationAPIController::class, 'readAuth'])->name('notifications.read.me');
      Route::patch('/notifications/read/all', [App\Http\Controllers\API\NotificationAPIController::class, 'readAll'])->name('notifications.read.all');
      Route::delete('/notifications/me/{notification}', [App\Http\Controllers\API\NotificationAPIController::class, 'destroy'])->name('notifications.destroy.me');

      // PayDunya Verify
      Route::post('/paydunya/verify/{giftCard}', [\App\Http\Controllers\PaydunyaController::class, 'verify'])->name('paydunya.verify');

      // Customer scope
      Route::group(['middleware' => ['role:customer|admin']], function () {
          Route::group(['middleware' => ['is_verified_phone']], function () {
              //Gift cards
              Route::post('/gift-cards', [App\Http\Controllers\API\GiftCardAPIController::class, 'storeAuth'])->middleware('idempotency')->name('gift-cards.store.me');
              Route::get('/gift-cards', [App\Http\Controllers\API\GiftCardAPIController::class, 'indexAuth'])->name('gift-cards.index.me');

              //Qr sessions
              Route::post('qr-sessions', [App\Http\Controllers\API\QRSessionAPIController::class, 'store'])->name('qr-sessions.store');
              Route::patch('qr-sessions', [App\Http\Controllers\API\QRSessionAPIController::class, 'verify'])->name('qr-sessions.verify');

              //Users
              Route::get('/customer/stats', [App\Http\Controllers\API\UserAPIController::class, 'statsCustomer'])->name('users.customers.stats'); //stats of the customer
          });
      });

      // Admin scope
      Route::group(['middleware' => ['role:admin']], function () {
          //Qr sessions resource
          Route::resource('qr-sessions', App\Http\Controllers\API\QRSessionAPIController::class)
              ->except(['store', 'update']); //list, show, destroy

          //Gift cards resource
          Route::get('/gift-cards/users/{user}', [App\Http\Controllers\API\GiftCardAPIController::class, 'index'])->name('gift-cards.index'); //List any gift cards via user id
          Route::post('/gift-cards/users/{user}', [App\Http\Controllers\API\GiftCardAPIController::class, 'store'])->middleware('idempotency')->name('gift-cards.store'); //Store any gift cards via user id
          Route::resource('gift-cards', App\Http\Controllers\API\GiftCardAPIController::class)
              ->except(['store', 'index']); //show, update, destroy

          //Users resource
          Route::get('/users', [App\Http\Controllers\API\UserAPIController::class, 'index'])->name('users.index');
          Route::patch('/users/{user}', [App\Http\Controllers\API\UserAPIController::class, 'update'])->name('users.update'); //Update any users

          //Categories resource
          Route::resource('categories', App\Http\Controllers\API\CategoryAPIController::class); //list, store, show, update, destroy

          //Designs resource
          Route::resource('designs', App\Http\Controllers\API\DesignAPIController::class); //list, store, show, update, destroy

          //Notifications resource
          Route::get('/notifications/users/{user}', [App\Http\Controllers\API\NotificationAPIController::class, 'index'])->name('notifications.index');
      });
});

Route::resource('invoices', App\Http\Controllers\API\InvoiceAPIController::class)
    ->except(['create', 'edit']);
