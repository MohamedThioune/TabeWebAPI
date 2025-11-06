<?php

namespace App\Infrastructure\Persistence;

use App\Models\GiftCard;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Collection;

class GiftCardRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'belonging_type',
        'type',
        'face_amount',
        'status',
        'expired_at',
        'limit',
        'skip',
        'owner_user_id',
        'beneficiary_id',
        'design_id'
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
