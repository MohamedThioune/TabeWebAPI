<?php

namespace App\Domain\GiftCards\Services;

use App\Infrastructure\External\PaymentGateway;
use App\Models\GiftCard;
use Illuminate\Support\Facades\Log;

class BuyCard
{

    public function __construct(private PaymentGateway $gateway){}

    public function execute(GiftCard $gift_card) : ?string
    {
        $amount = (int)$gift_card->face_amount;
        $user = $gift_card->user ?? null;

        $description = "Achat d'une carte d'un montant de {$amount}";
        $response = $this->gateway->charge($amount, $description);
        // $response = $this->gateway->quick_pay($amount, $user->email);
        Log::info('Response DTO', (array)$response);

        return $response->response_text ?? null;
        // return $response->url ?? null;
    }
}
