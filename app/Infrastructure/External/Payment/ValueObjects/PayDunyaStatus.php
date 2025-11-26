<?php

namespace App\Infrastructure\External\Payment\ValueObjects;

enum PayDunyaStatus: String
{
    case Pending = "pending";
    case Completed = "completed";
    case Failed = "failed";
}
