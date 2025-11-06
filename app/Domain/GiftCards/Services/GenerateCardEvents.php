<?php

namespace App\Domain\GiftCards\Services;

use App\Domain\GiftCards\Events\CardOperated;
use App\Infrastructure\Persistence\CardEventRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GenerateCardEvents
{
    public function __construct(private CardEventRepository $cardEventRepository){}
    /**
     * Handle the event.
     */
    public function handle(CardOperated $event)
    {
        try {
            $this->cardEventRepository->create($event->cardEvent->toArray());
        }
        catch (\Exception $e){
            DB::rollBack();
            $event->errorMessage['event'] = $e->getMessage();
        }
    }
}
