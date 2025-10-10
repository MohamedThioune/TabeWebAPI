<?php

namespace App\Domain\GiftCards\Services;

use App\Models\GiftCard as ModelCard;
use App\Domain\GiftCards\Events\CardGenerated;
use App\Infrastructure\Persistence\GiftCardRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CreateCard
{

    public function __construct(private GiftCardRepository $giftCardRepository){}
    /**
     * Handle the event.
     */
    public function handle(CardGenerated $event)
    {
        DB::beginTransaction();
        try {
            $card = $this->giftCardRepository->create($event->card->toArray());
            // Log::info($event->qrSession->toArray());
        }
        catch (\Exception $e){
            $event->errorMessage['card'] = $e->getMessage();
            DB::rollBack();
        }
    }
}
