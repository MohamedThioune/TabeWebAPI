<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; 
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * @OA\Schema(
 *      schema="Payout",
 *      required={"gross_amount","net_amount","status", "withdraw_mode", "user_id"},
 *      @OA\Property(
 *          property="gross_amount",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="integer",
 *          format="int32"
 *      ),
 *      @OA\Property(
 *          property="net_amount",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="integer",
 *          format="int32"
 *      ),
 *      @OA\Property(
 *          property="fees",
 *          description="",
 *          readOnly=false,
 *          nullable=true,
 *          type="integer",
 *          format="int32"
 *      ),
 *      @OA\Property(
 *          property="currency",
 *          description="",
 *          readOnly=false,
 *          nullable=true,
 *          type="string",
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
 *          property="withdraw_mode",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="string",
 *          enum={"paydunya", "orange-money-senegal", "wave-senegal", "expresso-senegal", "free-money-senegal"}
 *      ),
 *      @OA\Property(
 *          property="reference_number",
 *          description="",
 *          readOnly=false,
 *          nullable=true,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="transaction_id",
 *          description="",
 *          readOnly=false,
 *          nullable=true,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="partner_id",
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
class Payout extends Model
{
    use SoftDeletes, HasFactory, HasUuids;    
    public $table = 'payouts';

    public $fillable = [
        'gross_amount',
        'net_amount',
        'commentary',
        'fees',
        'currency',
        'status',
        'withdraw_mode',
        'reference_number',
        'transaction_id',
        'user_id'
    ];

    protected $casts = [
        'gross_amount' => 'integer',
        'net_amount' => 'integer',
        'fees' => 'integer',
        'currency' => 'string',
        'status' => 'string'
    ];

    public static array $rules = [
        'withdraw_mode' => 'required|string|in:paydunya,orange-money-senegal,wave-senegal,expresso-senegal,free-money-senegal',
        'commentary' => 'string',
    ];

    public static array $rules_listed = [
        'status' => 'string|in:authorized,completed,cancelled,failed',
        'filter_by_date' => 'string|in:today,week,month,year',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function payoutLines()
    {
        return $this->hasMany(PayoutLine::class);
    }

    public function transactions()
    {
        return $this->hasManyThrough(Transaction::class, PayoutLine::class, 'payout_id', 'id', 'id', 'transaction_id');
    }
    
}

