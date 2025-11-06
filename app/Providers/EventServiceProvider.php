<?php

namespace App\Providers;

use App\Domain\GiftCards\Events\CardOperated;
use App\Domain\GiftCards\Services\CreateCard;
use App\Domain\GiftCards\Services\GenerateCardEvents;
use App\Domain\GiftCards\Services\GenerateQr;
use App\Events\FileProcessed;
use App\Listeners\RegisterFileProcessed;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        CardOperated::class => [
           CreateCard::class,
           GenerateQr::class,
           GenerateCardEvents::class
        ],
        FileProcessed::class => [
            RegisterFileProcessed::class
        ]
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
