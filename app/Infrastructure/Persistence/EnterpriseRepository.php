<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Users\Entities\User;
use App\Models\Enterprise;

class EnterpriseRepository
{

    public function save(User $user): Enterprise
    {
        $model = Enterprise::create([
            'id' => $user->getEnterpriseId(),
            'name' => $user->getName(),
            'user_id' => $user->getId(),
        ]);

        return $model;
    }
}
