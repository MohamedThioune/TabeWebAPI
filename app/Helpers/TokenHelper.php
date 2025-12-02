<?php

namespace App\Helpers;

use Tuupola\Base62;
use Illuminate\Support\Str;

class TokenHelper
{
    public static function encodeUuid(string $data): string
    {
        $bytes = hex2bin(str_replace($data));
        return Base62::encode($bytes);
    }

    public static function decodeUuid(string $token): string
    {
        $bytes = Base62::decode($token);
        // $hex = bin2hex($bytes);

        // return sprintf(
        //     '%s-%s-%s-%s-%s',
        //     substr($hex, 0, 8),
        //     substr($hex, 8, 4),
        //     substr($hex, 12, 4),
        //     substr($hex, 16, 4),
        //     substr($hex, 20)
        // );
    }
}
