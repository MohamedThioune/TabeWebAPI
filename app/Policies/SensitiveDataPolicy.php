<?php

namespace App\Policies;

use App\Domain\Users\ValueObjects\Type;
use App\Models\User;

class SensitiveDataPolicy
{
    /**
     * Create a new policy instance.
     */
    public function seeSensitiveData(User $user, mixed $model = null): bool
    {
        if (! $model) {
            return $user->hasRole(Type::Admin->value);
        }

        $isOwner = ($model instanceof User) ?
            (string) $user->id === (string) $model?->id :
            (string) $user->id === (string) $model?->user_id;

        return $user->hasRole(Type::Admin->value) || $isOwner;
    }
}
