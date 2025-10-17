<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Users\Entities\User;
use App\Models\Customer;
use App\Repositories\BaseRepository;

class CustomerRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'id',
        'first_name',
        'last_name',
        'gender'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return Customer::class;
    }

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
