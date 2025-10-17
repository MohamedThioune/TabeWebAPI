<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Users\Entities\User;
use App\Models\User as ModelUser;
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
        return ModelUser::class;
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
}
