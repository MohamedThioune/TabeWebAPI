<?php

namespace App\Infrastructure\External\Payment;
use App\Models\GiftCard;

interface PaymentGateway
{
    public function post_callout(string $url, array $headers, array $payload);
    public function get_callout(string $url, array $headers, array $query = null);

    //Checkout invoice
    public function charge(int $amount, string $description, GiftCard $gift_card);

    //Quick pay
    public function quick_pay(int $amount, string $recipient_email, GiftCard $gift_card, string $recipient_phone = null, int $support_fees = 1, int $send_notification = 0);
    public function status_pay(string $reference_number, string $type_endpoint);

    //Payout refund
    public function initiate_refund(string $phone_number, int $amount, string $withdraw_mode);
    public function submit_refund(string $disburse_token, string $disburse_id = null);
}
