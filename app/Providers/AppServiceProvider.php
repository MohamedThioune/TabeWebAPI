<?php

namespace App\Providers;

use App\Channels\TwilioChannel;
use Illuminate\Support\Facades\DB;
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
        $loader = \Illuminate\Foundation\AliasLoader::getInstance();
        $loader->alias('Debugbar', \Barryvdh\Debugbar\Facades\Debugbar::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Alias new channel "Twilio"
        Notification::extend('twilio', function ($app) {
            return new TwilioChannel();
        });

        // Bind phone for the user request
        Route::bind('phone', function ($value) {
            // Normalize the numbers before searching
            $normalized = $this->normalizePhone($value);

            return \App\Models\User::where('phone', $normalized)->firstOrFail();
        });

        //Avoid destructive commands in production
        // DB::prohibitDestructiveCommands(app()->isProduction());
    }

    //Normalize the phone number
    public function normalizePhone(string $phone): string
    {
        // Save only the digits and "+" sign
        $clean = preg_replace('/[^\d\+]/', '', $phone);

        return $clean;
    }
}
