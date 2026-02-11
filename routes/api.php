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
Route::post('/gift-cards/verify/{nonce}', [\App\Http\Controllers\API\GiftCardAPIController::class, 'verifyToken'])->name('giftcards.verify.token');
Route::get('/partners', [App\Http\Controllers\API\UserAPIController::class, 'indexPartner'])->name('users.index.partner');

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
              Route::get('/gift-cards', [App\Http\Controllers\API\GiftCardAPIController::class, 'index'])->name('gift-cards.me.index');

              //Qr sessions
              Route::post('/qr-sessions', [App\Http\Controllers\API\QRSessionAPIController::class, 'store'])->name('qr-sessions.store');

              //Users
              Route::get('/customer/stats', [App\Http\Controllers\API\UserAPIController::class, 'statsCustomer'])->name('users.customers.stats'); //stats of the customer
           });
          Route::get('/invoices', [App\Http\Controllers\API\InvoiceAPIController::class, 'index'])->name('invoices.index');
          Route::put('/gift-cards/share/{giftCard}', [App\Http\Controllers\API\GiftCardAPIController::class, 'share'])->name('gift-cards.share');
      });


      //Partner scope
      Route::group(['middleware' => ['role:partner|admin']], function () {
            //Qr sessions
            Route::patch('/qr-sessions', [App\Http\Controllers\API\QRSessionAPIController::class, 'verify'])->name('qr-sessions.verify');

            //Gift cards
            Route::post('/users/verify/card', [App\Http\Controllers\API\GiftCardAPIController::class, 'verifyCode'])->name('giftcards.verify.code'); //verify a gift card code

            //Users
            Route::get('/partner/stats', [App\Http\Controllers\API\UserAPIController::class, 'statsPartner'])->name('users.partners.stats'); //stats of the partner

            //Transactions
            Route::get('/transactions', [App\Http\Controllers\API\TransactionAPIController::class, 'index'])->name('transactions.index');
            Route::post('/transactions', [App\Http\Controllers\API\TransactionAPIController::class, 'store'])->name('transactions.store');
            Route::post('/transactions/confirm/{transaction}', [App\Http\Controllers\API\TransactionAPIController::class, 'confirm'])->name('transactions.confirm');

            //Payouts
            Route::get('/payouts', [App\Http\Controllers\API\PayoutAPIController::class, 'index'])->name('payouts.index');
            Route::post('/payouts/before/request', [App\Http\Controllers\API\PayoutAPIController::class, 'beforeRequest'])->name('payouts.before_request');
            Route::post('/payouts/request', [App\Http\Controllers\API\PayoutAPIController::class, 'request'])->middleware('idempotency')->name('payouts.request');
            // Route::post('/payouts/submit', [App\Http\Controllers\API\PayoutAPIController::class, 'submit'])->middleware('idempotency')->name('payouts.submit');

      });

      // Admin scope
      Route::group(['middleware' => ['role:admin']], function () {
          //Qr sessions resource
          Route::resource('qr-sessions', App\Http\Controllers\API\QRSessionAPIController::class)
              ->except(['store', 'update']); //list, show, destroy

          //Gift cards resource
          Route::get('/gift-cards/all', [App\Http\Controllers\API\GiftCardAPIController::class, 'indexAdmin'])->name('gift-cards.admin.index'); //List any gift cards via user id
          Route::post('/gift-cards/users/{user}', [App\Http\Controllers\API\GiftCardAPIController::class, 'store'])->middleware('idempotency')->name('gift-cards.store'); //Store any gift cards via user id
          Route::resource('gift-cards', App\Http\Controllers\API\GiftCardAPIController::class)
              ->except(['store', 'index']); //show, update, destroy

          //Users resource
          Route::get('/users', [App\Http\Controllers\API\UserAPIController::class, 'index'])->name('users.index');
          Route::patch('/users/{user}', [App\Http\Controllers\API\UserAPIController::class, 'update'])->name('users.update'); //Update any users

          //Transaction resource
          Route::get('/transactions/all', [App\Http\Controllers\API\TransactionAPIController::class, 'indexAll'])->name('transactions.admin.index');

          //Payouts resource
          Route::get('/payouts/all', [App\Http\Controllers\API\PayoutAPIController::class, 'indexAll'])->name('payouts.admin.index');

          //Categories resource
          Route::resource('categories', App\Http\Controllers\API\CategoryAPIController::class); //list, store, show, update, destroy

          //Designs resource
          Route::resource('designs', App\Http\Controllers\API\DesignAPIController::class); //list, store, show, update, destroy

          //Stats
          Route::get('/admin/stats', [App\Http\Controllers\API\UserAPIController::class, 'statsAdmin'])->name('users.admin.stats'); //main stats 
          Route::get('/admin/stats/cards', [App\Http\Controllers\API\UserAPIController::class, 'statsAdminCards'])->name('users.admin.stats.cards'); //stats of the cards


      });
});