<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
 use Illuminate\Database\Eloquent\SoftDeletes; use Illuminate\Database\Eloquent\Factories\HasFactory;
/**
 * @OA\Schema(
 *      schema="UserCategory",
 *      required={"user_id","category_id"},
 *      @OA\Property(
 *          property="user_id",
 *          description="",
 *          readOnly=true,
 *          nullable=false,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="category_id",
 *          description="",
 *          readOnly=true,
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
 */
class UserCategory extends Model
{
    use HasUuids, SoftDeletes, HasFactory;
    public $table = 'user_categories';
    public $fillable = [
        'user_id',
        'category_id'
    ];

    protected $casts = [
    ];

    public static array $rules = [
        'user_id' => 'required|string|exists:partners,id',
        'category_id' => 'required|string|exists:categories,id'
    ];


}
