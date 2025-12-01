<?php

namespace App\Infrastructure\Persistence;

use App\Models\Invoice;
use App\Repositories\BaseRepository;

class InvoiceRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'type',
        'reference_number',
        'status',
        'endpoint',
        'user_id',
        'gift_card_id'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return Invoice::class;
    }
}
