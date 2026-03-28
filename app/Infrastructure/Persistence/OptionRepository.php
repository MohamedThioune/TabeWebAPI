<?php

namespace App\Infrastructure\Persistence;

use App\Models\Option;
use App\Repositories\BaseRepository;

class OptionRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'min_amount_card',
        'max_amount_card',
        'period_validity_card'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return Option::class;
    }
}
