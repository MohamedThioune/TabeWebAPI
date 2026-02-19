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

    public array $right_statuses = [
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
        $query->when(!$status, fn($query) => $query->whereIn('status', $this->right_statuses));
        $query->when($status, fn($query) => $query->where('status', $status));

        return $query->sum('face_amount');
    }

    // monthly stats(used card)
    public function usedMonthly(User $user) : int
    {
        $query = $user->gift_cards();
        $query->where('status', 'used')
            ->whereMonth('updated_at', date('m'));

        // dd($query->toSql());

        return $query->count();
    }

    public function find(string $id, array $columns = ['*'])
    {
        $query = $this->model->newQuery();
        $query->where('expired_at', '>', now());
        $query->where('status', 'active');
        $query->where('id', $id);

        return $query->first($columns);
    }

    public function findByCode(string $code, array $columns = ['*'])
    {
        $query = $this->model->newQuery();
        $query->where('expired_at', '>', now());
        $query->where('status', 'active');
        $query->where('code', $code);

        return $query->first($columns);
    }

    public function update(array $input, string $id)
    {
        $query = $this->model->newQuery();

        $model = $query->findOrFail($id);
        $exception_face_amount = [
            'used',
            'expired',
            'pending',
            'inactive'
        ];
        $exception_expired_date = [
            'active',
            'used',
            'pending',
            'inactive'
        ];
        
        //exception if the card is used 
        if (isset($input['face_amount']) && in_array($model->status, $exception_face_amount)) 
            throw new \Exception('Cannot update face amount of a used card');
        
        //exception on expiration date update if the card is active
        if (isset($input['expired_at']) && in_array($model->status, $exception_expired_date)) 
            throw new \Exception('Cannot update expiration date of an active card');
        
        $model->fill($input);

        $model->save();

        return $model;
    }
}
