<?php

namespace App\Infrastructure\Persistence;

use App\Models\GiftCard;
use App\Models\User;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Builder;

class GiftCardRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'code',
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

    public array $statuses = [
        'pending',
        'active',
        'used',
        'expired'    
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return GiftCard::class;
    }

    public function allQuery(array $search = [], int $skip = null, int $limit = null): Builder
    {
        $query = parent::allQuery($search, $skip, $limit);

        $query->whereIn('status', $this->statuses);

        return $query;
    }

    // total available cards, total used cards, total cards
    public function countQueryTotal(?string $status, User $user): int //status:active or null
    {
        $query = $user->gift_cards();
        $query->when(!$status, fn($query) => $query->whereIn('status', $this->statuses));
        $query->when($status, fn($query) => $query->where('status', $status));

        return $query->count();
    }

    // total available cards amount, total cards amount
    public function countQueryAmount(?string $status, User $user): int //status:active or null
    {
        $query = $user->gift_cards();
        $query->when(!$status, fn($query) => $query->whereIn('status', $this->statuses));
        $query->when($status, fn($query) => $query->where('status', $status));

        return $query->sum('face_amount');
    }

    //monthly stats(used card)
    public function usedMonthly(User $user) : int
    {
        $query = $user->gift_cards();
        $query->where('status', 'used')
              ->whereHas('qrSessions', function($qr_query){
                  $qr_query->where('status', 'used');
                  $qr_query->whereMonth('updated_at', date('m'));
              });

        // dd($query->toSql());

        return $query->count();
    }
}
