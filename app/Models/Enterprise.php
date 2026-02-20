<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; 
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Models\Beneficiairy;

/**
 * @OA\Schema(
 *      schema="Enterprise",
 *      required={"name","phone"},
 *      @OA\Property(
 *          property="name",
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
 *          property="size",
 *          description="",
 *          readOnly=true,
 *          nullable=true,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="sector",
 *          description="",
 *          readOnly=false,
 *          nullable=true,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="address",
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
*/
class Enterprise extends Model
{
    use HasUuids, SoftDeletes, HasFactory;    
    public $table = 'enterprises';

    public $fillable = [
        'name',
        'phone',
        'size',
        'sector',
        'address'
    ];

    public static array $rules = [
        'name' => 'required|string',
        'phone' => 'required|string'
    ];

    public function beneficiairies(){
        return $this->hasMany(Beneficiairy::class);
    }

}
