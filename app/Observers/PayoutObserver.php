<?php

namespace App\Observers;

use App\Models\Payout;

class PayoutObserver
{
    /**
     * Handle the Payout "created" event.
     */
    public function created(Payout $payout): void
    {

    }

    /**
     * Handle the Payout "updated" event.
     */
    public function updated(Payout $payout): void
    {
        $payout->updated_at = now();
        $payout->save();
    }

    /**
     * Handle the Payout "deleted" event.
     */
    public function deleted(Payout $payout): void
    {
        //
    }

    /**
     * Handle the Payout "restored" event.
     */
    public function restored(Payout $payout): void
    {
        //
    }

    /**
     * Handle the Payout "force deleted" event.
     */
    public function forceDeleted(Payout $payout): void
    {
        //
    }
}
