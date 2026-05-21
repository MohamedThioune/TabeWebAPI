<?php

namespace App\Infrastructure\External\Payment\ValueObjects;

enum PayDunyaStatus: string
{
    case Pending = 'pending';
    case Completed = 'completed';
    case Failed = 'failed';
}
