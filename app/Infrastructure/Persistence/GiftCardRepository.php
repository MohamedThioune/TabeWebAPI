<?php

namespace App\Infrastructure\Persistence;

use App\Models\GiftCard;
use App\Repositories\BaseRepository;

class GiftCardRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'face_amount'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return GiftCard::class;
    }
}
