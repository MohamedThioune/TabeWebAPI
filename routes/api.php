<?php

use App\Http\Controllers\API\AuthAPIController;
use App\Http\Controllers\API\CategoryAPIController;
use App\Http\Controllers\API\DesignAPIController;
use App\Http\Controllers\API\EnterpriseAPIController;
use App\Http\Controllers\API\FileAPIController;
use App\Http\Controllers\API\GiftCardAPIController;
use App\Http\Controllers\API\InvoiceAPIController;
use App\Http\Controllers\API\NotificationAPIController;
use App\Http\Controllers\API\OptionAPIController;
use App\Http\Controllers\API\PayoutAPIController;
use App\Http\Controllers\API\QRSessionAPIController;
use App\Http\Controllers\API\TransactionAPIController;
use App\Http\Controllers\API\UserAPIController;
use App\Http\Controllers\PaydunyaController;
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
Route::get('/paydunya/return/success', [PaydunyaController::class, 'return_success'])->middleware('throttle:20,1')->name('paydunya.return.success');
Route::post('/paydunya/ipn', [PaydunyaController::class, 'ipn_handle'])->name('paydunya.ipn');
Route::post('/gift-cards/verify/{nonce}', [GiftCardAPIController::class, 'verifyToken'])->name('giftcards.verify.token');
Route::get('/partners', [UserAPIController::class, 'indexPartner'])->name('users.index.partner');

Route::group(['prefix' => 'auth'], function () {
    Route::post('/register', [AuthAPIController::class, 'register'])->name('auth.register');
    Route::post('/otp/request/{phone}', [AuthAPIController::class, 'otp_request'])->name('auth.otp.request');
    Route::put('/otp/verify/{phone}', [AuthAPIController::class, 'otp_verify'])->middleware('throttle:20,1')->name('auth.otp.verify');
    Route::patch('/reset/password/{phone}', [AuthAPIController::class, 'reset_password'])->name('auth.reset_password');
});

Route::group(['middleware' => ['auth:api']], function () {
    // Oauth user
    Route::get('/me', [AuthAPIController::class, 'me'])->name('auth.me');
    Route::delete('/oauth/logout', [AuthAPIController::class, 'logout'])->name('auth.logout');
    Route::delete('/me', [UserAPIController::class, 'destroy'])->name('auth.delete'); // delete all data relatives to the connected user !!
    Route::patch('/update/password', [UserAPIController::class, 'update_password'])->name('auth.modify_password');

    // User actions (list partner, update user, upload file, notifications)
    Route::patch('/users', [UserAPIController::class, 'updateAuth'])->name('users.update.me');
    Route::post('/file/upload', [FileAPIController::class, 'upload'])->name('files.upload');

    // Notifications (get notifications, read notification, read all notifications, delete notification)
    Route::get('/notifications/me', [NotificationAPIController::class, 'indexAuth'])->name('notifications.me');
    Route::patch('/notifications/me/{notification}', [NotificationAPIController::class, 'readAuth'])->name('notifications.read.me');
    Route::patch('/notifications/read/all', [NotificationAPIController::class, 'readAll'])->name('notifications.read.all');
    Route::delete('/notifications/me/{notification}', [NotificationAPIController::class, 'destroy'])->name('notifications.destroy.me');

    // PayDunya Verify
    Route::post('/paydunya/verify/{giftCard}', [PaydunyaController::class, 'verify'])->name('paydunya.verify');

    // Customer scope
    Route::group(['middleware' => ['role:customer|admin']], function () {
        Route::group(['middleware' => ['is_verified_phone']], function () {
            // Gift cards
            Route::post('/gift-cards', [GiftCardAPIController::class, 'storeAuth'])->middleware('idempotency')->name('gift-cards.store.me');
            Route::get('/gift-cards', [GiftCardAPIController::class, 'index'])->name('gift-cards.me.index');

            // Qr sessions
            Route::post('/qr-sessions', [QRSessionAPIController::class, 'store'])->name('qr-sessions.store');

            // Users
            Route::get('/customer/stats', [UserAPIController::class, 'statsCustomer'])->name('users.customers.stats'); // stats of the customer
        });
        Route::get('/invoices', [InvoiceAPIController::class, 'index'])->name('invoices.index');
        Route::put('/gift-cards/share/{giftCard}', [GiftCardAPIController::class, 'share'])->name('gift-cards.share');
    });

    // Partner scope
    Route::group(['middleware' => ['role:partner|admin']], function () {
        // Qr sessions
        Route::patch('/qr-sessions', [QRSessionAPIController::class, 'verify'])->name('qr-sessions.verify');

        // Gift cards
        Route::post('/users/verify/card', [GiftCardAPIController::class, 'verifyCode'])->name('giftcards.verify.code'); // verify a gift card code

        // Users
        Route::get('/partner/stats', [UserAPIController::class, 'statsPartner'])->name('users.partners.stats'); // stats of the partner

        // Transactions
        Route::get('/transactions', [TransactionAPIController::class, 'index'])->name('transactions.index');
        Route::post('/transactions', [TransactionAPIController::class, 'store'])->name('transactions.store');
        Route::post('/transactions/confirm/{transaction}', [TransactionAPIController::class, 'confirm'])->name('transactions.confirm');

        // Payouts
        Route::get('/payouts', [PayoutAPIController::class, 'index'])->name('payouts.index');
        Route::post('/payouts/before/request', [PayoutAPIController::class, 'beforeRequest'])->name('payouts.before_request');
        Route::post('/payouts/request', [PayoutAPIController::class, 'request'])->middleware('idempotency')->name('payouts.request');
        Route::post('/payouts/submit/{payout}', [PayoutAPIController::class, 'submit'])->middleware('idempotency')->name('payouts.submit');

    });

    // Admin scope
    Route::group(['middleware' => ['role:admin']], function () {
        // Qr sessions resource
        Route::resource('qr-sessions', QRSessionAPIController::class)
            ->except(['store', 'update']); // list, show, destroy

        // Gift cards resource
        Route::get('/gift-cards/all', [GiftCardAPIController::class, 'indexAdmin'])->name('gift-cards.admin.index'); // List any gift cards via user id
        Route::post('/gift-cards/users/{user}', [GiftCardAPIController::class, 'store'])->middleware('idempotency')->name('gift-cards.store'); // Store any gift cards via user id
        Route::resource('gift-cards', GiftCardAPIController::class)
            ->except(['store', 'index']); // show, update, destroy
        Route::post('/gift-cards/deactivate/{giftCard}', [GiftCardAPIController::class, 'deactivate'])->name('gift-cards.deactivate'); // deactivate a gift card (soft delete)

        // Users resource
        Route::get('/users', [UserAPIController::class, 'index'])->name('users.index');
        Route::patch('/users/{user}', [UserAPIController::class, 'update'])->name('users.update'); // Update any users

        // Transaction resource
        Route::get('/transactions/all', [TransactionAPIController::class, 'indexAll'])->name('transactions.admin.index');

        // Payouts resource
        Route::get('/payouts/all', [PayoutAPIController::class, 'indexAll'])->name('payouts.admin.index');

        // Categories resource
        Route::resource('categories', CategoryAPIController::class); // list, store, show, update, destroy

        // Designs resource
        Route::resource('designs', DesignAPIController::class); // list, store, show, update, destroy

        // Enterprises resource
        Route::resource('enterprises', EnterpriseAPIController::class);

        // Stats
        Route::get('/admin/stats', [UserAPIController::class, 'statsAdmin'])->name('users.admin.stats'); // main stats
        Route::get('/admin/stats/weekly', [UserAPIController::class, 'weeklyTransactionStats'])->name('users.admin.stats.weekly'); // stats of the weekly transactions
        Route::get('/admin/stats/cards', [UserAPIController::class, 'statsAdminCards'])->name('users.admin.stats.cards'); // stats of the cards
        Route::get('/admin/stats/activity', [UserAPIController::class, 'statsActivityPartners'])->name('users.admin.stats.activities'); // stats of the partners activity
        Route::get('/admin/stats/partners', [UserAPIController::class, 'statsAdminPartners'])->name('users.admin.stats.partners'); // stats of the partners general

        // Options
        Route::get('/options', [OptionAPIController::class, 'index'])->name('options.index');
        Route::patch('/options', [OptionAPIController::class, 'update'])->name('options.update');
    });
});
