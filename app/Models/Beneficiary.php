<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; 
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Enterprise;

/**
 * @OA\Schema(
 *      schema="Beneficiary",
 *      required={"full_name","phone"},
 *      @OA\Property(
 *          property="full_name",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="phone",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="email",
 *          description="",
 *          readOnly=false,
 *          nullable=true,
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
*/
class Beneficiary extends Model
{
    use HasUuids, SoftDeletes, HasFactory;
    public $table = 'beneficiaries';

    public $fillable = [
        'full_name',
        'phone',
        'email',
        'enterprise_id'
    ];

    protected $casts = [
        'full_name' => 'string',
        'phone' => 'string',
        'email' => 'string'
    ];

    public static array $rules = [
        'full_name' => 'required|string|max:255',
        'phone' => 'required|string|max:255',
        'email' => 'string|email|max:255'
    ];

    public function GiftCards(){
        return $this->hasMany(GiftCard::class);
    }

    public function Enterprise(){
        return $this->belongsTo(Enterprise::class);
    }

}
