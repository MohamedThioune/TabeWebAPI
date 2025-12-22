<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Helpers\CodeGenerator;

/**
 * @OA\Schema(
 *      schema="GiftCard",
 *      required={"belonging_type","type","face_amount","is_active","design_id"},
 *      @OA\Property(
 *          property="code",
 *          description="Unique code of the gift card",
 *          readOnly=true,
 *          nullable=true,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="belonging_type",
 *          description="myself or others",
 *          readOnly=false,
 *          nullable=false,
 *          type="string",
 *      ),
 *      @OA\Property(
 *           property="type",
 *           description="physical or digital",
 *           readOnly=false,
 *           nullable=false,
 *           type="string",
 *       ),
 *      @OA\Property(
 *          property="face_amount",
 *          description="amount of the card between [10000, 150000] ",
 *          readOnly=false,
 *          nullable=false,
 *          type="integer",
 *          format="int32"
 *      ),
 *      @OA\Property(
 *           property="is_active",
 *           description="state of the card",
 *           readOnly=true,
 *           nullable=false,
 *           type="boolean",
 *       ),
 *       @OA\Property(
 *            property="owner_user_id",
 *            description="creator of the card",
 *            readOnly=true,
 *            nullable=false,
 *            type="string",
 *      ),
 *      @OA\Property(
 *            property="beneficiary_id",
 *            description="beneficiary of the card",
 *            readOnly=true,
 *            nullable=false,
 *            type="integer",
 *      ),
 *      @OA\Property(
 *           property="design_id",
 *           description="designs of the card(1:Classique, 2:Moderne, 3:Elegant, 4:Premium)",
 *           readOnly=false,
 *           nullable=false,
 *           type="integer",
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

class GiftCard extends Model
{
    use HasUuids, SoftDeletes, HasFactory;

    public $table = 'gift_cards';

    public $fillable = [
        'id',
        'code',
        'belonging_type', //me or others
        'type', //physical or digital
        'face_amount',
        'status', //active, inactive, used, expired
        'expired_at',
        'issued_via',
        'owner_user_id',
        'beneficiary_id',
        'design_id'
    ];

    protected $casts = [
        'face_amount' => 'integer',
        'is_verified' => 'boolean',
        'with_summary' => 'boolean',
    ];

    protected $hidden = [
    ];

    public static array $rules = [
        'belonging_type' => 'required|string|in:myself,others',
        'type' => 'required|string|in:physical,digital',
        'face_amount' => 'required|integer|between:10000,150000',
        'design_id' => 'required|integer|exists:designs,id'
    ];

    public static array $rules_listed = [
        'code' => 'string',
        'status' => 'string|in:active,inactive,used,expired,pending',
        'belonging_type' => 'string|in:myself,others',
        'type' => 'string|in:physical,digital',
        'skip' => 'integer|gt:0',
        'limit' => 'integer|gt:0',
        'with_summary' => 'boolean',
    ];

    public static function verifyRules(): array
    {
        return [
            'code' => [
                'required',
                'string',
                'exists:gift_cards,code',
                function ($attribute, $value, $fail) {
                    if(!CodeGenerator::isValid($value)){ 
                        $fail('The gift card is not valid or falsified.');
                    }
                }
            ],
        ];
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($card) {
            do {
                $code = CodeGenerator::generate();
            } while (self::where('code', $code)->exists());

            $card->code = $code;
        });
    }

    public function user(){
        return $this->belongsTo(User::class, 'owner_user_id', 'id');
    }
    public function qrSessions(){
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
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
    public function latest_invoice(string $endpoint)
    {
        return $this->hasMany(Invoice::class)->where('endpoint', $endpoint)->where('type', 'Achat de carte')->latest('created_at')->first();
    }
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
