<?php

namespace App\Infrastructure\Persistence;

use App\Models\CardEvent;
use App\Repositories\BaseRepository;
use App\Models\User;

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

    //monthly stats(used card)
    // public function usedMonthly(User $user) : int
    // {
    //     $query = $user->gift_cards();
    //     $query->where('status', 'used')
    //           ->whereHas('qrSessions', function($qr_query){
    //               $qr_query->where('status', 'used');
    //               $qr_query->whereMonth('updated_at', date('m'));
    //           });

    //     // dd($query->toSql());

    //     return $query->count();
    // }
}
