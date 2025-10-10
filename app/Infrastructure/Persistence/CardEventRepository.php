<?php

namespace App\Infrastructure\Persistence;

use App\Models\CardEvent;
use App\Repositories\BaseRepository;

class CardEventRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'type'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return CardEvent::class;
    }
}
