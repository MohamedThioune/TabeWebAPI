<?php

namespace App\Helpers;

use App\Domain\Users\ValueObjects\Type;
use App\Models\User;

class FindChannel
{
    public function __invoke(User $user): string
    {
        $role = $user->roles->pluck('name')->toArray()[0] ?? Type::Customer->value;

        $channel = match ($role) {
            Type::Customer->value => 'notifs.client.'.$user->id,
            Type::Partner->value => 'notifs.merchant.'.$user->id,
            Type::Admin->value => 'notifs.admin.'.$user->id,
            default => 'notifs.client.'.$user->id,
        };

        return $channel;
    }
}
