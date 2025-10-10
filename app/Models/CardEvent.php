<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
 use Illuminate\Database\Eloquent\SoftDeletes; use Illuminate\Database\Eloquent\Factories\HasFactory;
/**
 * @OA\Schema(
 *      schema="CardEvent",
 *      required={"type"},
 *      @OA\Property(
 *          property="type",
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
 */class CardEvent extends Model
{
    use HasUuids, SoftDeletes, HasFactory;
    public $table = 'card_events';

    public $fillable = [
        'type',
        'gift_card_id',
        'meta_json'
    ];

    protected $casts = [
        'type' => 'string',
        'meta_json' => 'array'
    ];

    public static array $rules = [
        'type' => 'required|string|in:activated,issued,used,expired,blocked',
        'gift_card_id' => 'required|exists:gift_cards,id',
    ];

    public function giftcard(){
        return $this->belongsTo(GiftCard::class);
    }

}
