<?php

namespace App\Infrastructure\External;
interface PaymentGateway
{
    public static function charge(int $amount, string $description);

    // public function refund(int $amount, string $description);
}
