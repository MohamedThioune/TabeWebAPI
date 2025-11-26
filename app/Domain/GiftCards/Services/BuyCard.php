<?php

namespace App\Domain\GiftCards\Services;

use App\Infrastructure\External\Payment\PaymentGateway;
use App\Models\GiftCard;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BuyCard
{

    public function __construct(private PaymentGateway $gateway){}

    public function execute(GiftCard $gift_card) : ?string
    {
        $amount = (int)$gift_card->face_amount;
        $user = $gift_card->user ?? null;
        if(!$user)
            return null;

        $description = "Achat d'une carte d'un montant de {$amount}";
        $response = tap($this->gateway->charge($amount, $description, $gift_card),
            function ($response) {
                Log::info('Response DTO', (array)$response);
            });
        // $response = $this->gateway->quick_pay($amount, $user->email);

        // register the invoice
        $user->invoices()->create([
            'id' => Str::uuid()->toString(),
            'amount' => $amount,
            'reference_number' => $response->reference_number ?: $response->token,
            'status' => $response->status ?: 'pending',
            'endpoint' => 'checkout',
            'gift_card_id' => $gift_card->id
        ]);

        return $response->response_text ?? null;
        // return $response->url ?? null;
    }
}
