<?php

namespace App\Policies;

use App\Domain\Users\ValueObjects\Type;
use App\Models\User;
use App\Models\Partner;
use App\Models\Customer;
use App\Models\Enterprise;
use App\Models\File;

class SensitiveDataPolicy
{
    /**
     * Create a new policy instance.
     */

    public function seeSensitiveData(User $user, mixed $model = null): bool
    {
        if (!$model)
            return $user->hasRole(Type::Admin->value);

        $isOwner = ($model instanceof User) ?
            (String) $user->id === (String) $model?->id :
            (String) $user->id === (String) $model?->user_id;

        return $user->hasRole(Type::Admin->value) || $isOwner;
    }

}
