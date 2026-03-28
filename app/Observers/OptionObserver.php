<?php

namespace App\Observers;

use App\Models\Option;

class OptionObserver
{
    /**
     * Handle the Option "created" event.
     */
    public function created(Option $option): void
    {
        cache()->forget('app_option');
    }

    /**
     * Handle the Option "updated" event.
     */
    public function updated(Option $option): void
    {
        cache()->forget('app_option');
    }

    public function saved(Option $option): void
    {
        cache()->forget('app_option');
    }

    /**
     * Handle the Option "deleted" event.
     */
    public function deleted(Option $option): void
    {
        cache()->forget('app_option');

    }

    /**
     * Handle the Option "restored" event.
     */
    public function restored(Option $option): void
    {
        //
    }

    /**
     * Handle the Option "force deleted" event.
     */
    public function forceDeleted(Option $option): void
    {
        cache()->forget('app_option');
    }
}
