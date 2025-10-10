<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Users\Entities\User;
use App\Models\Customer;


class CustomerRepository
{

    public function save(User $user): Customer
    {
        $model = Customer::create([
            'id' => $user->getCustomerId(),
            'first_name' => $user->getFirstName(),
            'last_name' => $user->getLastName(),
            'gender' => $user->getGender(),
            'user_id' => $user->getId(),
        ]);

        return $model;
    }
}
