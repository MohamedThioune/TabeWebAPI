<?php

namespace App\Infrastructure\Persistence;

use App\Models\Beneficiary;
use App\Repositories\BaseRepository;

class BeneficiaryRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'fullname',
        'phone',
        'email'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return Beneficiary::class;
    }
}
