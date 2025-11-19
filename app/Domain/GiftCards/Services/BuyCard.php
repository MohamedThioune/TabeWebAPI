<?php

namespace App\Domain\GiftCards\Services;

use App\Infrastructure\External\PaydunyaGateway;
use App\Models\GiftCard;

class BuyCard
{

    public function __construct(){}

    public function execute(GiftCard $gift_card){
        $amount = (int)$gift_card->face_amount;
        $user = $gift_card->user ?? null;
        $customer = $user?->customer()->first();

        $description = "Achat d'une carte d'un montant de {$amount}";
        $customer = [
            'name' => $customer->first_name . ' ' . $customer->last_name,
            'email' => $user->email,
            'phone' => $user->phone
        ];

        PaydunyaGateway::charge($amount, $description, $customer);
    }
}
