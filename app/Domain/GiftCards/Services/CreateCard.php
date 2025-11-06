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

            $user = $card->user;
            $beneficiary = $card->beneficiary;
//            if($card->belonging_type == "others"){
//                $format_beneficiary = $beneficiary->full_name;
//                $format_customer = !empty($user->customer) ? $user->customer[0]->first_name . ' ' . $user->customer[0]->last_name : '';
//                $format_amount = Number::format($card->face_amount, locale: 'sv'); //Swedish format (ex: 10 000)
//                $content_variables = json_encode(["1" => $format_beneficiary, "2" => $format_customer, "3" => $format_amount]);
//                $content = "";
//
//                $node = new Node(
//                    content: $content,
//                    contentVariables: $content_variables,
//                    level: "Info",
//                    model: "card",
//                    title: "Nouvelle carte créée",
//                    body: "Une carte-cadeau de {$format_amount} FCFA a été créée avec succès pour {$format_beneficiary}"
//                );
//                $user->notify(new PushCardNotification($node, $beneficiary->phone, "whatsapp"));
//            }
        }
        catch (\Exception $e){
            $event->errorMessage['card'] = $e->getMessage();
            DB::rollBack();
        }
    }
}
