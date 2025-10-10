<?php

namespace App\Domain\GiftCards\UseCases;

use App\Domain\GiftCards\Entities\Card;
use App\Domain\GiftCards\ValueObjects\CardEvent;
use App\Domain\GiftCards\ValueObjects\QrSession;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Domain\GiftCards\Events\CardGenerated;

class CardFullyGenerated
{

    public function __construct(){}

    public function execute(array $dto){
        // Instance of card, qr_session, card_event
        try {
            //New Card
            $card = new Card(
                id : Str::uuid()->toString(),
                belonging_type: $dto['belonging_type'],
                pin_hash: $dto['pin_hash'],
                face_amount: $dto['face_amount'],
                pin_mask: $dto['pin_mask'],
                expired_at: now()->addMonths(3),
                owner_user_id: $dto['owner_user_id'],
                beneficiary_id: $dto['beneficiary_id'],
                design_id: $dto['design_id'],
            );

            //New QR session
            $uuid_qr = Str::uuid()->toString();
            $qr_hashed_url = self::qr_url($uuid_qr);
            $hashed = $qr_hashed_url['hashedUuid'] ?? null;
            $url = $qr_hashed_url['url'] ?? null;
            $qr_session = new QrSession(
                id : $uuid_qr,
                token: $hashed,
                url: $url,
                expired_at: now()->addDays(2),
                gift_card_id: $card->getId(),
            );

            //New Card Event
            $card_event = new CardEvent(
                id : Str::uuid()->toString(),
                type : "activated",
                gift_card_id: $card->getId(),
                meta_json: null
            );

            //Use event
            $event = new CardGenerated(
               card:  $card,
               qrSession: $qr_session,
               cardEvent: $card_event,
               errorMessage: null
            );
            event($event);
            return $event;
        }
        catch (\Exception $e){
            return $e->getMessage();
        }
    }

    public static function qr_url(string $uuid_qr) : array
    {
        $signature = hash_hmac('sha256', $uuid_qr, config('app.key'));
        $hashedUuid = Hash::make($uuid_qr); //database security

        // Signed URL to be encoded in the QR code
        $url = config('app.frontend_url') . "/scan?uuid={$uuid_qr}&sig={$signature}";

        return [
            'hashedUuid' => $hashedUuid,
            "url" => $url,
        ];
    }
}
