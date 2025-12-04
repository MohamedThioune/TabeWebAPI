<?php

namespace App\Infrastructure\Persistence;

use App\Models\Invoice;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Builder;

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

    public function allQuery(array $search = [], int $skip = null, int $limit = null): Builder
    {
        $query = parent::allQuery($search, $skip, $limit);
        $q = $search['q'] ?? null;

        $query->whereHas('gift_card', function ($gift_card_query) use ($q) {
            $gift_card_query->whereHas('beneficiary', function ($beneficiary_query) use ($q) {
                $beneficiary_query->where('full_name', 'LIKE', '%' . trim($q) .'%');
            });
        });
        $query->orWhere('amount', 'LIKE', '%' . trim($q) .'%');

        return $query;
    }
}
