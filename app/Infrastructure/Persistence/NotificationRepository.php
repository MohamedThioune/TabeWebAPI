<?php

namespace App\Infrastructure\Persistence;

use App\Models\Notification;
use App\Repositories\BaseRepository;
use Carbon\Carbon;

class NotificationRepository extends BaseRepository
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
        return Notification::class;
    }

}
