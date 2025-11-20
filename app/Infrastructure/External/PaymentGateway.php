<?php

namespace App\Infrastructure\External;
interface PaymentGateway
{
    public function post_callout(string $url, array $headers, array $payload);

    //Checkout invoice
    public function charge(int $amount, string $description);

    //Quick pay
    public function quick_pay(int $amount, string $recipient_email, string $recipient_phone = null, int $support_fees = 1, int $send_notification = 0);

    // public function refund(int $amount, string $description);
}
