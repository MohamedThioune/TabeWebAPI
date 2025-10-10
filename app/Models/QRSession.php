<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
 use Illuminate\Database\Eloquent\SoftDeletes; use Illuminate\Database\Eloquent\Factories\HasFactory;
/**
 * @OA\Schema(
 *      schema="QRSession",
 *      required={"token","url"},
 *      @OA\Property(
 *          property="token",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="url",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="expired_at",
 *          description="",
 *          readOnly=false,
 *          nullable=true,
 *          type="string",
 *          format="date-time"
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
class QRSession extends Model
{
     use HasUuids, SoftDeletes, HasFactory;
     public $table = 'qr_sessions';

    public $fillable = [
        'token',
        'url',
        'expired_at',
        'gift_card_id'
    ];

    protected $casts = [
        'token' => 'string',
        'url' => 'string'
    ];

    public static array $rules = [
        'token' => 'required|string',
        'expired_at' => 'date',
        'gift_card_id' => 'string'

    ];

    public function giftcard(){
        return $this->belongsTo(GiftCard::class);
    }

}
