<?php

namespace App\Infrastructure\Persistence;

use App\Models\GiftCard;
use App\Models\User;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

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
        'inactive',
        'used',
        'expired'    
    ];

    public array $right_statuses = [
        'active',
        'used',
        'expired'    
    ];

    public array $other_statuses = [
        'used', 
        'pending', 
        'inactive'
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
        $status = $search["status"] ?? null;

        unset($search["status"]);
        $query = parent::allQuery($search, $skip, $limit);

        $query->when(!$status, fn($query) => $query->whereIn('status', $this->statuses));
        $query->when($status && $status === "active", fn($query) => $query->where('status', $status)->where('expired_at', '>', now()));
        $query->when($status && $status !== "active", fn($query) => $query->where('status', $status));

        // var_dump($query->toSql());
        // die();
        return $query;
    }

    // total available cards, total used cards, total cards
    public function countQueryTotal(?string $status, User $user): int //status:active or null
    {
        $query = $user->gift_cards();
        $query->when(!$status, fn($query) => $query->whereIn('status', $this->statuses));
        $query->when($status && $status === "active", fn($query) => $query->where('status', $status)->where('expired_at', '>', now()));
        $query->when($status && $status !== "active", fn($query) => $query->where('status', $status));

        return $query->count();
    }

    // total cards amount
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
        $qrRepository = new QRSessionRepository();
        $query = $this->model->newQuery();

        $model = $query->findOrFail($id);
        $exception_face_amount = [
            'used',
            'expired',
            'pending',
            'inactive'
        ];
        $exception_expired_date = [
            'used',
            'pending',
            'inactive'
        ];
        
        //exception if the card is used 
        if (isset($input['face_amount'])) 
            throw new \Exception('Cannot update face amount due to fraud risk');
        
        //exception on expiration date update if the card is active
        if (isset($input['expired_at']) && in_array($model->status, $exception_expired_date)) 
            throw new \Exception('Cannot update expiration date of an used,pending or inactive card');

        DB::beginTransaction();
        // If the expiration date is being updated, we need to update the related QR session as well
        if (isset($input['expired_at'] )) {
            $qr = $model->qrSessions()->latest('created_at')->first();
            $qrRepository->update(['expired_at' => $input['expired_at']], $qr->id);
        }
    
        $model->fill($input);
        $model->save();
        DB::commit();

        return $model;
    }
}
