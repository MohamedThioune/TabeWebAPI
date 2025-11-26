<?php

namespace App\Domain\GiftCards\Services;

use App\Infrastructure\External\Payment\PaymentGateway;
use App\Models\GiftCard;
use Illuminate\Support\Facades\Log;

class CheckStatusPaymentCard
{
    public function __construct(private PaymentGateway $gateway){}

    public function execute(GiftCard $gift_card, String $endpoint = "checkout") : mixed
    {
        $invoice = $gift_card->latest_invoice($endpoint);
        $reference_number = $invoice?->reference_number ?: null;

        if(!$reference_number)
            return null;

        $response = tap($this->gateway->status_pay($reference_number, $endpoint),
            function ($response) {
                Log::info('Response DTO', (array)$response);
            });

        return $response ?: null;
    }

}
