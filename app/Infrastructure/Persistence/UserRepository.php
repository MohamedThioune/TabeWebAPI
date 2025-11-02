<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Users\Entities\User;
use App\Models\User as ModelUser;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class UserRepository extends BaseRepository
{
    protected $fieldSearchable = [
//        'phone',
//        'email',
//        'whatsApp',
//        'country',
//        'city',
//        'address',
//        'bio',
//        'website',
        'is_active',
        'phone_verified_at',
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return ModelUser::class;
    }

    /**
     * @Override All Query method
     */
    public function allQuery(array $search = [], int $skip = null, int $limit = null): Builder
    {
        $query = $this->model->newQuery();

        $role = isset($search['type']) ? $search['type'] : null;

        // sector or q(name of the partner) query
        $sector = $search['sector'] ?? null;
        $q = $search['q'] ?? null;

        if($role == "partner")
            $query->whereHas('partner', function ($partner_query) use ($search, $sector, $q) {
                //sector is defined
                if($sector)
                    $partner_query->where('sector', $search['sector']);

                //q is defined for the name partner
                if($q)
                    $partner_query->where('name', 'LIKE', '%' . trim($q) .'%');
            });

        // q(name of the categories belonged to the user) query
        if($q)
            $query->orWhereHas('categories', function ($category_query) use ($search, $q, $query) {
                $category_query->where('name', 'LIKE', '%' . trim($q) .'%');
            });

        //research (active user state, ...) queries
        if (count($search)) {
            foreach($search as $key => $value) {
                if (in_array($key, $this->getFieldsSearchable())) {
                    $query->where($key, $value);
                }
            }
        }

        // phone verified query
        if(isset($search['is_phone_verified']))
            ($search['is_phone_verified'])
                ? $query->whereNotNull('phone_verified_at')
                : $query->whereNull('phone_verified_at');

        // role user (customer, partner, enterprise) query
        if($role)
            $query->role($role);

        if (!is_null($skip)) {
            $query->skip($skip);
        }

        if (!is_null($limit)) {
            $query->limit($limit);
        }

        $query->orderBy('created_at', 'desc');
        return $query;
    }

    /**
     * @Override Update method
     */
    public function update(array $input, string $id)
    {
        $query = $this->model->newQuery();

        $model = $query->findOrFail($id);

        $model->fill($input);

        if(isset($input['categories']))
            $model->categories()->syncWithoutDetaching($input['categories']);

        $model->save();

        return $model;
    }

    public function save(User $user): ModelUser
    {
        $model = ModelUser::create([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'phone' => (String)$user->getPhone(),
            'whatsApp' => "whatsapp:" . (String)$user->getwhatsApp(),
            'password' => $user->getPasswordHash(),
        ]);

        //Assign role
        $model->assignRole($user->getType());

        return $model->load($user->getType());
    }

}
