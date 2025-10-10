<?php

namespace App\Infrastructure\Persistence;

use App\Models\QRSession;
use App\Repositories\BaseRepository;

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
        return QRSession::class;
    }
}
