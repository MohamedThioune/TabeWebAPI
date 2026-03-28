<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * @OA\Schema(
 *      schema="Option",
 *      required={"min_amount_card","max_amount_card","period_validity_card"},
 *      @OA\Property(
 *          property="min_amount_card",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="integer",
 *          format="int32"
 *      ),
 *      @OA\Property(
 *          property="max_amount_card",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="integer",
 *          format="int32"
 *      ),
 *      @OA\Property(
 *          property="period_validity_card",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="integer",
 *          format="int32"
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
 *      ),
 *      @OA\Property(
 *          property="deleted_at",
 *          description="",
 *          readOnly=true,
 *          nullable=true,
 *          type="string",
 *          format="date-time"
 *      ),
 * 
 * )
 */
class Option extends Model
{
    use SoftDeletes, HasFactory, HasUuids;    
    public $table = 'options';

    public $fillable = [
        'min_amount_card',
        'max_amount_card',
        'period_validity_card'
    ];

    protected $casts = [
        'max_amount_card' => 'integer',
        'max_amount_card' => 'integer',
        'period_validity_card' => 'integer'
    ];

    public static array $rules = [
        'min_amount_card' => 'integer|min:1000|max:100000',
        'max_amount_card' => 'integer|min:100000|max:1000000',
        'period_validity_card' => 'integer|min:1|max:12'
    ];
    
}
