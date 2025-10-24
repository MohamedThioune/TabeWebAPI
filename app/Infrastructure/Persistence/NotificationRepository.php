<?php

namespace App\Infrastructure\Persistence;

use App\Models\Notification;
use App\Repositories\BaseRepository;

class NotificationRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'data',
        'notifiable_id',
        'is_read',
        'read_at',
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
