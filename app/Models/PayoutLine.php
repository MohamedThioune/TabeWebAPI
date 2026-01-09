<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; 
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * @OA\Schema(
 *      schema="PayoutLine",
 *      required={"transaction_id","payout_id"},
 *      @OA\Property(
 *          property="transaction_id",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="payout_id",
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
 */
class PayoutLine extends Model
{
    use SoftDeletes, HasFactory, HasUuids;   
    public $table = 'payout_lines';

    public $fillable = [
        'transaction_id',
        'payout_id'
    ];

    protected $casts = [
        'transaction_id' => 'string',
        'payout_id' => 'string'
    ];

    public static array $rules = [
        'transaction_id' => 'required|exists:transactions,id',
        'payout_id' => 'required|exists:payouts,id'
    ];

    
    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

     public function payout()
    {
        return $this->belongsTo(Payout::class);
    }
}
