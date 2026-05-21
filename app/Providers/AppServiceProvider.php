<?php

namespace App\Providers;

use App\Channels\TwilioChannel;
use App\Infrastructure\External\Payment\PaydunyaGateway;
use App\Infrastructure\External\Payment\PaymentGateway;
use App\Models\Option;
use App\Models\Payout;
use App\Models\User;
use App\Observers\OptionObserver;
use App\Observers\PayoutObserver;
use Barryvdh\Debugbar\Facades\Debugbar;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider;
use Notification;
use Route;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $loader = AliasLoader::getInstance();
        $loader->alias('Debugbar', Debugbar::class);

        $this->app->bind(
            PaymentGateway::class,
            PaydunyaGateway::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->environment('production') && ! file_exists(storage_path('oauth-private.key'))) {
            \Artisan::call('passport:keys');
        }
        // Alias new channel "Twilio"
        Notification::extend('twilio', function ($app) {
            return new TwilioChannel;
        });

        // Bind phone for the user request
        Route::bind('phone', function ($value) {
            // Normalize the numbers before searching
            $normalized = $this->normalizePhone($value);

            return User::where('phone', $normalized)->firstOrFail();
        });

        // Avoid destructive commands in production
        // DB::prohibitDestructiveCommands(app()->isProduction());

        // Observer for models
        Option::observe(OptionObserver::class);
        Payout::observe(PayoutObserver::class);

    }

    // Normalize the phone number
    public function normalizePhone(string $phone): string
    {
        // Save only the digits and "+" sign
        $clean = preg_replace('/[^\d\+]/', '', $phone);

        return $clean;
    }
}
