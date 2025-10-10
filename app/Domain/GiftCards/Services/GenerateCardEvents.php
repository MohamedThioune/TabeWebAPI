<?php

namespace App\Domain\GiftCards\Services;

use App\Domain\GiftCards\Events\CardGenerated;
use App\Infrastructure\Persistence\CardEventRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GenerateCardEvents
{
    public function __construct(private CardEventRepository $cardEventRepository){}
    /**
     * Handle the event.
     */
    public function handle(CardGenerated $event)
    {
        try {
            $this->cardEventRepository->create($event->cardEvent->toArray());
            // Log::info($event->qrSession->toArray());
        }
        catch (\Exception $e){
            DB::rollBack();
            $event->errorMessage['event'] = $e->getMessage();
        }
    }
}
