<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


/**
 * @OA\Schema(
 *      schema="Notification",
 *      required={},
 *      @OA\Property(
 *          property="title",
 *          description="title of the notification",
 *          readOnly=true,
 *          nullable=false,
 *          type="string",
 *      ),
 *      @OA\Property(
 *           property="body",
 *           description="body of the notification",
 *           readOnly=true,
 *           nullable=false,
 *           type="string",
 *           format="textarea"
 *      ),
 *      @OA\Property(
 *            property="level",
 *            description="level of the notification",
 *            readOnly=true,
 *            nullable=false,
 *            type="string",
 *            enum={"Important", "Urgent", "Info"},
 *       ),
 *       @OA\Property(
 *            property="model",
 *            description="model of the notification",
 *            readOnly=true,
 *            nullable=false,
 *            type="string",
 *            enum={"transaction", "card", "profile", "maintenance"},
 *       ),
 *      @OA\Property(
 *           property="notifiable_id",
 *           description="",
 *           readOnly=true,
 *           nullable=false,
 *           type="string",
 *     ),
 *     @OA\Property(
 *           property="is_read",
 *           description="",
 *           readOnly=true,
 *           nullable=false,
 *           type="boolean",
 *      ),
 *     @OA\Property(
 *           property="read_at",
 *           description="",
 *           readOnly=true,
 *           nullable=true,
 *           type="string",
 *           format="date-time"
 *       ),
 *      @OA\Property(
 *          property="created_at",
 *          description="",
 *          readOnly=true,
 *          nullable=true,
 *          type="string",
 *          format="date-time"
 *      ),
 *      @OA\Property(
 *          property="updated_at",
 *          description="",
 *          readOnly=true,
 *          nullable=true,
 *          type="string",
 *          format="date-time"
 *      )
 * )
 * @method static where(string $string, mixed $id)
 */
//transaction, card, profile, maintenance
//Important, Urgent, Info
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
