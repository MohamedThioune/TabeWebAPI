<?php

namespace App\Infrastructure\Persistence;

use App\Models\PayoutLine;
use App\Repositories\BaseRepository;

class PayoutLineRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'transaction_id',
        'payout_id'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return PayoutLine::class;
    }
}
