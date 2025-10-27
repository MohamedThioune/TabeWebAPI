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

        $isOwner = property_exists($model, 'user_id') && $user->id === $model->user_id;
        return $user->hasRole(Type::Admin->value) || $isOwner;
    }

    public function seeMySensitiveData(User $user, User $model): bool
    {

        $isOwner = property_exists($model, 'user_id') && $user->id === $model->id;
        return $user->hasRole(Type::Admin->value) || $isOwner;
    }

}
