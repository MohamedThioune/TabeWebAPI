<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasUuids;

    protected $table = 'notifications';

    protected $fillable = [
        'data',
        'notifiable_id',
        'is_read',
        'read_at',
    ];

    public static array $ruleListed = [
        'is_read' => 'boolean',
    ];
}
