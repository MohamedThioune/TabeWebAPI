<?php

namespace App\Infrastructure\Persistence;

use App\Models\Payout;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Builder;

class PayoutRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'gross_amount',
        'net_amount',
        'fees',
        'currency',
        'status',
        'user_id',
        'reference_number',
        'transaction_id'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return Payout::class;
    }

    public function getPayoutInProgressByUser(string $userId = null): ?Builder
    {
        $query = $this->model::query();
        return $query->when($userId, function (Builder $q) use ($userId) {
            $q->where('user_id', $userId);
        })->where('status', 'authorized');
    }

    public function getPayoutCompletedByUser(string $userId = null): ?Builder
    {
        $query = $this->model::query();
        return $query->when($userId, function (Builder $q) use ($userId) {
            $q->where('user_id', $userId);
        })->where('status', 'completed');
    }

    public function getPayoutCancelledByUser(string $userId = null): ?Builder
    {
        $query = $this->model::query();
        return $query->when($userId, function (Builder $q) use ($userId) {
            $q->where('user_id', $userId);
        })->where('status', 'cancelled');
    }

    public function getPayoutFailedByUser(string $userId = null): ?Builder
    {
        $query = $this->model::query();
        return $query->when($userId, function (Builder $q) use ($userId) {
            $q->where('user_id', $userId);
        })->where('status', 'failed');
    }
}
