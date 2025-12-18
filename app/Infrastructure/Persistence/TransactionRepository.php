<?php

namespace App\Infrastructure\Persistence;

use App\Models\Transaction;
use App\Repositories\BaseRepository;

class TransactionRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'currency',
        'status',
        'amount',
        'user_id',
        'gift_card_id'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return Transaction::class;
    }

    public function last_transaction_for_gift_card($gift_card_id)
    {
        return Transaction::where('gift_card_id', $gift_card_id)
            ->orderBy('created_at', 'desc')
            ->first();
    }
}
