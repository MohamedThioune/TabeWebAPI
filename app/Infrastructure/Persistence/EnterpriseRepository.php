<?php

namespace App\Infrastructure\Persistence;

use App\Models\Enterprise;
use App\Repositories\BaseRepository;

class EnterpriseRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'name',
        'phone',
        'size',
        'sector',
        'address'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return Enterprise::class;
    }
}
