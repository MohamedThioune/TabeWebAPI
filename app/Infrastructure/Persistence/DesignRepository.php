<?php

namespace App\Infrastructure\Persistence;

use App\Models\Design;
use App\Repositories\BaseRepository;

class DesignRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'name'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return Design::class;
    }
}
