<?php

namespace App\Infrastructure\Persistence;

use App\Models\User;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Collection;

class UserRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'phone',
        'email',
        'whatsApp',
        'country',
        'city',
        'address',
        'bio',
        'website',
        'is_active',
        'phone_verified_at',
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return User::class;
    }

    /**
     * @Override All method
     */
    public function all(array $search = [], int $skip = null, int $limit = null, array $columns = ['*']): Collection
    {
        $query = $this->allQuery($search, $skip, $limit);

        if(isset($search['type']))
            $query->role($search['type']);

        if(isset($search['is_phone_verified']))
            ($search['is_phone_verified'])
                ? $query->whereNotNull('phone_verified_at')
                : $query->whereNull('phone_verified_at');

        return $query->get($columns);
    }
}
