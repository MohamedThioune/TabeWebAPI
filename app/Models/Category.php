<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @OA\Schema(
 *      schema="Category",
 *      required={"name"},
 *      @OA\Property(
 *          property="name",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="string",
 *      ),
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
 * @method static pluck(string $string)
 * @method static firstOrCreate(string[] $array)
 */
class Category extends Model
{
    use HasUuids, SoftDeletes, HasFactory;
    public $table = 'categories';

    public $fillable = [
        'name'
    ];

    protected $casts = [
    ];

    public static array $rules = [
        'name' => 'required|string|max:255'
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_categories')
            ->withTimestamps();
    }


}
