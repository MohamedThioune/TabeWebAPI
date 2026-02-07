<?php

namespace App\Infrastructure\Persistence;

use App\Models\Transaction;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Builder; 

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
        return $this->model::where('gift_card_id', $gift_card_id)
            ->orderBy('created_at', 'desc')
            ->first();
    }

    public function allQuery(array $search = [], int $skip = null, int $limit = null): Builder
    {
        $query = parent::allQuery($search, $skip, $limit);

        //Search by code and beneficiary name of related gift card, if provided
        $q = $search['q'] ?? null;
        $query->when($q, function (Builder $query) use ($q) {
            $query->orWhere('amount', 'like', '%' . $q . '%');
            $query->whereHas('gift_card', function (Builder $query_card) use ($q) {

                $query_card->orWhere('code', 'like', '%' . $q . '%');
                $query_card->whereHas('beneficiary', function (Builder $query_beneficiary) use ($q) {
                    $query_beneficiary->orWhere('full_name', 'like', '%' . $q . '%');
                });
                
            });
        });  

        return $query;
    }

    public function getCapturedTransactionsByUser(string $user_id = null): Builder
    {
        $query = $this->model::query();
        return $query->when($user_id, function (Builder $q) use ($user_id) {
            $q->where('user_id', $user_id);
        })->where('status', 'captured');
    }

    public function getAuthorizedTransactionsByUser(string $user_id = null): Builder
    {
        $query = $this->model::query();
        return $query->when($user_id, function (Builder $q) use ($user_id) {
            $q->where('user_id', $user_id);
        })->where('status', 'authorized');
    }

    public function getRefundedTransactionsByUser(string $user_id = null): Builder
    {
        $query = $this->model::query();
        return $query->when($user_id, function (Builder $q) use ($user_id) {
            $q->where('user_id', $user_id);
        })->where('status', 'refunded');
    }

    public function getFailedTransactionsByUser(string $user_id = null): Builder
    {
        $query = $this->model::query();
        return $query->when($user_id, function (Builder $q) use ($user_id) {
            $q->where('user_id', $user_id);
        })->where('status', 'failed');
    }
}
