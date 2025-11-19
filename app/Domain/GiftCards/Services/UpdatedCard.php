<?php

namespace App\Domain\GiftCards\Services;

use App\Domain\GiftCards\Events\CardOperated;
use App\Domain\GiftCards\ValueObjects\CardEvent;
use App\Models\GiftCard;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UpdatedCard
{

    public function __construct(){}


    /**
     * @throws \Throwable
     */
    public static function execute(GiftCard $gift_card, String $status): void
    {
        DB::beginTransaction();
        try {
            // Change the card status
            $gift_card->status = $status;
            $gift_card->save();

            // Log the card events
            $card_event = new CardEvent(
                id: Str::uuid()->toString(),
                type: $status,
                gift_card_id: $gift_card->id,
                meta_json: null
            );
            event(
                new CardOperated (
                    card: null,
                    qrSession: null,
                    cardEvent: $card_event,
                    errorMessage: null
                )
            );
            DB::commit();
        }catch(\Exception $e){
            DB::rollBack();
            throw $e;
        }

        //Notify me after card used
    }

}
