<?php

namespace App\Helpers;
use App\Models\Option;

class Parameter
{
    public static function option(): ?Option
    {
        return cache()->remember('app_option', 3600, function () {
            return Option::latest('created_at')->first();
        });
    }

    public static function minAmountCard(): int
    {
        return self::option()?->min_amount_card
            ?? config('parameter.card.min_amount');
    }

    public static function maxAmountCard(): int
    {
        return self::option()?->max_amount_card
            ?? config('parameter.card.max_amount');
    }

    public static function periodValidityCard(): int
    {
        return self::option()?->period_validity_card
            ?? config('parameter.card.period_validity');
    }
}