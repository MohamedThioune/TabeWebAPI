<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @OA\Schema(
 *      schema="QRSession",
 *      required={"gift_card_id"},
 *      @OA\Property(
 *           property="status",
 *           description="",
 *           readOnly=true,
 *           nullable=true,
 *           type="string",
 *       ),
 *      @OA\Property(
 *          property="payload",
 *          description="",
 *          readOnly=true,
 *          nullable=true,
 *          type="string",
 *          format="byte"
 *      ),
 *      @OA\Property(
 *          property="url",
 *          description="",
 *          readOnly=true,
 *          nullable=false,
 *          type="string",
 *          format="uri"
 *      ),
 *      @OA\Property(
 *          property="expired_at",
 *          description="",
 *          readOnly=true,
 *          nullable=true,
 *          type="string",
 *          format="date-time"
 *      ),
 *     @OA\Property(
 *           property="gift_card_id",
 *           description="",
 *           readOnly=false,
 *           nullable=true,
 *           type="string",
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
 */
class QrSession extends Model
{
    use HasUuids, SoftDeletes, HasFactory;
    public $table = 'qr_sessions';

    public $fillable = [
        'id',
        'status',
        'token',
        'url',
        'expired_at',
        'gift_card_id'
    ];

    protected $casts = [
        'url' => 'string',
        'status' => 'string',
    ];

    public static array $rules = [
        'gift_card_id' => 'required|string|exists:gift_cards,id'
    ];

    public static array $rules_verify = [
        'payload' => 'required|string|exists:qr_sessions,token'
    ];

    public function giftCard(){
        return $this->belongsTo(GiftCard::class , 'gift_card_id');
    }

}
