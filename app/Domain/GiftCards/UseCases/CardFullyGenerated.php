<?php

namespace App\Domain\GiftCards\UseCases;

use App\Domain\GiftCards\Entities\Card;
use App\Domain\GiftCards\ValueObjects\CardEvent;
use App\Domain\GiftCards\ValueObjects\QrSession;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Domain\GiftCards\Events\CardOperated;
use App\Helpers\TokenHelper;
use Tuupola\Base62;

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
                type: $dto['type'],
                face_amount: $dto['face_amount'],
                expired_at: now()->addMonths(3),
                owner_user_id: $dto['owner_user_id'],
                beneficiary_id: $dto['beneficiary_id'],
                design_id: $dto['design_id'],
            );

            //New QR session
            $uuid_qr = Str::uuid()->toString();
            $qr_hashed_url = self::qr_url($uuid_qr);
            $payload = $qr_hashed_url['payload'] ?? null;
            $url = $qr_hashed_url['url'] ?? null;
            $qr_session = new QrSession(
                id : $uuid_qr,
                token: $payload,
                url: $url,
                expired_at: now()->addMonths(3),
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
            $event = new CardOperated(
               card:  $card,
               qrSession: $qr_session,
               cardEvent: $card_event,
               errorMessage: null
            );
            event($event);
            return $event;
        }
        catch (\Exception $e){
            Log::error($e->getMessage());
            return $e->getMessage();
        }
    }

    public static function qr_url(string $uuid_qr) : array
    {
        $nonce = Str::random(1);
        $payload = self::encoding_payload($uuid_qr, $nonce);

        // Signed URL to be encoded in the QR code
        $url = config('app.partner_url') . "/scan/{$payload}";

        return [
            'payload' => $payload,
            "url" => $url,
        ];
    }

    public static function encoding_payload(string $data, $nonce = null) : ?string
    {
        $nonce = $nonce ?: random_bytes(4); // 4 bytes nonce

        $signature = hash_hmac('sha256', $data, config('app.key'), true);
        $combined = $data . '.' . $nonce . '.' . $signature;
        // $payload = base64url_encode($combined);
        $base62 = new Base62();
        $payload = $base62->encode($combined);

        return $payload;
    }

    public static function check($payload): ?string
    {
        // $decoded = base64_decode($payload);
        $base62 = new Base62();
        $decoded = $base62->decode($payload);
        list($uuid, $nonce, $signature) = explode('.', $decoded, 3);
        if (!hash_equals(hash_hmac('sha256', $uuid, config('app.key'), true), $signature)) {
            return null;
        }

        return (string)$uuid;
    }

    
}
