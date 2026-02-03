<?php

namespace App\Domain\GiftCards\Services;

use App\Infrastructure\External\Payment\PaymentGateway;
use App\Models\GiftCard;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BuyCard
{

    public function __construct(private PaymentGateway $gateway){}

    public function execute(GiftCard $gift_card) : ?object
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

        //$reponse->url !null
        if(!$response || !$response->response_text)
            return null;

        $reference = $response->reference_number ?: $response->token;
        try {
            // Register the invoice
            $user->invoices()->create([
                'id' => Str::uuid()->toString(),
                'type' => 'Achat de carte',
                'amount' => $amount,
                'reference_number' => $reference,
                'status' => $response->status ?: 'pending',
                'endpoint' => 'checkout',
                'gift_card_id' => $gift_card->id
            ]);

        } catch (\Exception $e) {
            Log::error('Error logging payment response: ' . $e->getMessage());
        }
       
        return (Object)['reference' => $reference, 'url' => $response->response_text];
    }
}
