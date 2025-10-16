<?php

namespace App\Infrastructure\Persistence;

use App\Models\UserCategory;
use App\Repositories\BaseRepository;

class PartnerCategoryRepository extends BaseRepository
{
    protected $fieldSearchable = [

    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return UserCategory::class;
    }
}
