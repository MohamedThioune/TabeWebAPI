<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @OA\Schema(
 *      schema="GiftCard",
 *      required={"pin_hash","face_amount", "expired_at"},
 *      @OA\Property(
 *          property="pin_hash",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="face_amount",
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
 *      )
 * )
 */

class GiftCard extends Model
{
    use HasUuids, SoftDeletes, HasFactory;
    public $table = 'gift_cards';

    public $fillable = [
        'id',
        'belonging_type', //me or others
        'face_amount',
        'pin_mask',
        'pin_hash',
        'is_active',
        'expired_at',
        'owner_user_id',
        'beneficiary_id',
        'design_id'
    ];

    protected $casts = [
        'face_amount' => 'integer'
    ];

    protected $hidden = [
        'pin_hash'
    ];

    public static array $rules = [
        'pin' => 'required|string',
        'belonging_type' => 'required|string|in:myself,others',
        'face_amount' => 'required|integer|between:10000,150000',
        'is_active' => 'boolean',
//        'design_id' => 'required|integer|exists:designs,id'
    ];

    public function user(){
        return $this->belongsTo(User::class, 'owner_user_id');
    }
    public function qrsessions(){
        return $this->hasMany(QrSession::class);
    }
    public function beneficiary()
    {
        return $this->belongsTo(Beneficiary::class);
    }
    public function design(){
        return $this->belongsTo(Design::class);
    }
    public function cardevent(){
        return $this->hasMany(CardEvent::class);
    }


}
