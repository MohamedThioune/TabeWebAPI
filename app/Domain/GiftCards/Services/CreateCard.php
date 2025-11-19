<?php

namespace App\Domain\GiftCards\Services;

use App\Domain\Users\DTO\Node;
use App\Models\GiftCard as ModelCard;
use App\Domain\GiftCards\Events\CardOperated;
use App\Infrastructure\Persistence\GiftCardRepository;
use App\Notifications\PushCardNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Number;
use Illuminate\Support\Str;

class CreateCard
{

    public function __construct(private GiftCardRepository $giftCardRepository){}
    /**
     * Handle the event.
     */
    public function handle(CardOperated $event)
    {
        DB::beginTransaction();
        try {
            //card creation
            $card = $this->giftCardRepository->create($event->card->toArray());
        }
        catch (\Exception $e){
            $event->errorMessage['card'] = $e->getMessage();
            DB::rollBack();
        }
    }
}
