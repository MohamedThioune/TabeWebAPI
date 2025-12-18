<?php

namespace App\Infrastructure\Persistence;

use App\Models\CardEvent;
use App\Repositories\BaseRepository;

class CardEventRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'type',
        'gift_card_id',
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
