<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Users\Entities\User;
use App\Models\Partner;

class PartnerRepository
{
    public function save(User $user): Partner
    {
        $model = Partner::create([
            'id' => $user->getPartnerId(),
            'name' => $user->getName(),
            'user_id' => $user->getId(),
        ]);

        //Load relation
        // $model->load('user');

        return $model;
    }
}
