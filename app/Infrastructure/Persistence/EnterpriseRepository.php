<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Users\Entities\User;
use App\Models\Enterprise;
use App\Repositories\BaseRepository;

class EnterpriseRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'id',
        'name'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return Enterprise::class;
    }

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
