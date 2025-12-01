<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
 use Illuminate\Database\Eloquent\SoftDeletes; use Illuminate\Database\Eloquent\Factories\HasFactory;
/**
 * @OA\Schema(
 *      schema="Invoice",
 *      required={"type", "reference_number", "amount", "status", "gift_card_id"},
 *      @OA\Property(
 *          property="type",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="string",
 *          enum={"Achat de carte", "Paiement en boutique"}
 *      ),
 *      @OA\Property(
 *          property="reference_number",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="token",
 *          description="",
 *          readOnly=false,
 *          nullable=true,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="status",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="string",
 *      ),
 *     @OA\Property(
 *           property="amount",
 *           description="",
 *           readOnly=false,
 *           nullable=false,
 *           type="integer",
 *       ),
 *      @OA\Property(
 *          property="endpoint",
 *          description="",
 *          readOnly=false,
 *          nullable=true,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="user_id",
 *          description="",
 *          readOnly=false,
 *          nullable=true,
 *          type="string",
 *      ),
 *     @OA\Property(
 *           property="gift_card_id",
 *           description="",
 *           readOnly=false,
 *           nullable=false,
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
 * @method static where(string $string, string $reference_number)
 */
class Invoice extends Model
{
    use SoftDeletes, HasFactory;
    public $table = 'invoices';

    public $fillable = [
        'id',
        'type',
        'reference_number',
        'status',
        'amount',
        'endpoint',
        'receipt_url',
        'user_id',
        'gift_card_id'
    ];

    protected $casts = [
        'reference_number' => 'string',
        'amount' => 'integer',
        'token' => 'string',
        'status' => 'string',
        'endpoint' => 'string',
    ];

    public static array $rules = [
        'type' => 'required|string|in:Achat de carte,Paiement en boutique',
        'status' => 'required|string|in:pending,completed,failed',
        'amount' => 'required|integer|min:10000',
        'receipt_url' => 'nullable|url',
        'user_id' => 'nullable|exists:users,id',
        'gift_card_id' => 'required|exists:gift_cards,id',
    ];

    public function gift_card(){
        return $this->belongsTo(GiftCard::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }


}
