<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes; 
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @OA\Schema(
 *      schema="Transaction",
 *      required={"status", "amount", "gift_card_id", "user_id"},
 *      @OA\Property(
 *          property="currency",
 *          description="",
 *          readOnly=false,
 *          nullable=true,
 *          type="string",
 *      ),
 *      @OA\Property(
 *         property="amount",
 *         description="",
 *         readOnly=false,
 *         nullable=false,
 *         type="integer",
 *      ),
 *      @OA\Property(
 *          property="status",
 *          description="",
 *          readOnly=true,
 *          nullable=false,
 *          type="string",
 *          enum={"authorized", "captured", "cancelled", "refunded", "failed"}
 *      ),
 *      @OA\Property(
 *          property="gift_card_id",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="string",
 *      ),
 *      @OA\Property(
 *        property="user_id",
 *        description="",
 *        readOnly=false,        
 *        nullable=false,
 *        type="string",
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

class Transaction extends Model
{
    use SoftDeletes, HasFactory, HasUuids;    
    
    public $table = 'transactions';

    public $fillable = [
        'status',
        'amount',
        'currency',
        'user_id',
        'gift_card_id'
    ];

    protected $casts = [
        'currency' => 'string',
    ];

    public static array $rules = [
        'status' => 'string|in:authorized,captured,cancelled,refunded,failed',
        'amount' => 'required|integer|min:10000|max:150000',
        'gift_card_id' => 'required|string|exists:gift_cards,id',
    ];

    public static array $rules_confirm = [
        'otp_code' => 'string|min:6|max:6',
        'action' => 'required|string|in:cancel,confirm'
    ];

    public static array $rules_listed = [
        'status' => 'string|in:authorized,captured,cancelled,refunded,failed',
        'filter_by_date' => 'string|in:today,week,month,year',
    ];

    public function gift_card()
    {
        return $this->belongsTo(GiftCard::class);
    }
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
