<?php

namespace App\Providers;

use App\Channels\TwilioChannel;
use Illuminate\Support\ServiceProvider;
use Notification;

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
        //
        Notification::extend('twilio', function ($app) {
            return new TwilioChannel();
        });

    }
}
