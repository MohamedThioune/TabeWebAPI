<?php

namespace App\Infrastructure\Persistence;
use App\Domain\Users\Entities\User;
use App\Models\User as ModelUser;

class UserRepository
{
    public function save(User $user): ModelUser
    {
        $model = ModelUser::create([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'phone' => (String)$user->getPhone(),
            'whatsApp' => "whatsapp:" . (String)$user->getwhatsApp(),
            'password' => $user->getPasswordHash(),
        ]);

        //Assign role
        $model->assignRole($user->getType());

        return $model->load($user->getType());
    }
}
