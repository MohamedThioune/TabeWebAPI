<?php

namespace App\Infrastructure\Persistence;

use App\Models\QrSession;
use App\Models\User as ModelUser;
use App\Repositories\BaseRepository;
use Carbon\Carbon;

class QRSessionRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'expired_at'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return QrSession::class;
    }

    /**
     * (Override) Find model record for given id
     */
    public function find(string $id, array $columns = ['*'])
    {
        $query = $this->model->newQuery();
        $query->where('status', 'pending')
              ->where('expired_at', '>', Carbon::now());

        return $query->find($id, $columns);
    }
}
