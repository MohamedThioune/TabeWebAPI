<?php

namespace App\Domain\Users\ValueObjects;

enum Type: string
{
    case Admin = 'admin';

    case Customer = 'customer';
    case Partner = 'partner';

    case Enterprise = 'enterprise';
}
