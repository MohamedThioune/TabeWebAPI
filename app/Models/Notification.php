<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


/**
 * @method static where(string $string, mixed $id)
 */
class Notification extends Model
{
    use HasUuids, Softdeletes;

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
