<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Validation\Rules\Enum;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use App\Domain\Users\ValueObjects\Type;

/**
 * @method static find(string $user_id)
 * @method static create(array $user)
 * @method static pluck(string $string)
*/

/**
 * @OA\Schema(
 *      schema="User",
 *      required={"email","phone","whatsApp","password"},
 *      @OA\Property(
 *           property="sigla",
 *           description="",
 *           readOnly=true,
 *           nullable=false,
 *           type="string",
 *       ),
 *      @OA\Property(
 *          property="email",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="string",
 *          format="email"
 *      ),
 *      @OA\Property(
 *          property="phone",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="string",
 *      ),
 *      @OA\Property(
 *           property="whatsApp",
 *           description="",
 *           readOnly=false,
 *           nullable=false,
 *           type="string",
 *       ),
 *      @OA\Property(
 *            property="password",
 *            description="",
 *            readOnly=false,
 *            nullable=false,
 *            type="string",
 *            format="password"
 *        ),
 *      @OA\Property(
 *            property="bio",
 *            description="",
 *            readOnly=false,
 *            nullable=true,
 *            type="string",
 *            format="textarea"
 *        ),
 *      @OA\Property(
 *             property="website",
 *             description="",
 *             readOnly=false,
 *             nullable=true,
 *             type="string",
 *             format="uri"
 *         ),
 *      @OA\Property(
 *          property="country",
 *          description="",
 *          readOnly=false,
 *          nullable=true,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="city",
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
 *            property="is_active",
 *            description="",
 *            readOnly=true,
 *            nullable=true,
 *            type="boolean",
 *        ),
 *      @OA\Property(
 *            property="phone_verified_at",
 *            description="",
 *            readOnly=true,
 *            nullable=true,
 *            type="string",
 *            format="date-time"
 *        ),
 *     @OA\Property(
 *           property="user_registered_at",
 *           description="",
 *           readOnly=true,
 *           nullable=true,
 *           type="string",
 *       ),
 *      @OA\Property(
 *           property="created_at",
 *           description="",
 *           readOnly=true,
 *           nullable=true,
 *           type="string",
 *           format="date-time"
 *       ),
 *       @OA\Property(
 *           property="updated_at",
 *           description="",
 *           readOnly=true,
 *           nullable=true,
 *           type="string",
 *           format="date-time"
 *       )
 * )
 */
class User extends Authenticatable
{
    use HasUuids, HasApiTokens, HasRoles, HasFactory, Notifiable, SoftDeletes;

    protected $table = 'users';
    protected $guard_name = 'api'; // ou 'api' selon ton auth

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = array(
        'id',
        'email',
        'phone',
        'whatsApp',
        'password',
        'bio',
        'website',
        'country',
        'city',
        'address',
        'is_active',
        'phone_verified_at',
    );

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
//        'phone_verified_at' => 'datetime',
    ];

    public function whatsAppTwilio(): string
    {
        return $this->whatsApp ? $this->whatsApp : '';
    }

    public static function ruleCreated(): array
    {
        return [
            'type' => ["required", "string", new Enum(Type::class)],
            'email' => ["required", "string", "email", "unique:users,email"],
            'phone' => ["required", "string", "unique:users,phone"],
            'whatsApp' => ["required", "string", "unique:users,whatsApp"],
            'password' => ["required", "string", "min:8", "confirmed"],
            'is_active' => ["boolean"],
        ];
    }

    public static function ruleListed(): array
    {
        return [
            'type' => ["string", new Enum(Type::class)],
            'sector' => ["string", "in:Mode,Beauté,Gastronomie,Technologie,Bien-être,Décoration,Sport,Librairie"],
            'q' => ["string", "max:255"],
            'is_phone_verified' => ["boolean"],
            'is_active' => ["boolean"],
            'country' => ["string", "max:255"],
            'city' => ["string", "max:255"],
            'address' => ["string", "max:255"]
        ];
    }

    public static array $ruleUpdated = [
        'email' => "string|email|unique:users,email",
        'website' => "string|url",
        'bio' => "string",
        'categories' => "array",
        'country' => "string|max:255",
        'city' => "string|max:255",
        'address' => "string|max:255",
    ];

    public static array $resetPassword = [
        'otp_code' => 'required|string|min:6|max:6',
        'new_password' => ["required", "string", "min:8", "confirmed"],
    ];

    public static array $modifyPassword = [
        'password' => 'required|string|min:8',
        'new_password' => ["required", "string", "min:8", "confirmed"],
    ];

    public function findForPassport(string $username): User
    {
        $user = $this->where('phone', $username)->first();

        if (! $user)
            throw \Illuminate\Validation\ValidationException::withMessages([
                "phone" => "Phone number not found on records",
            ]);

        return $user;
    }

    //Relationship query builder
    public function customer(){
        return $this->hasMany(Customer::class);
    }
    public function enterprise(){
        return $this->hasMany(Enterprise::class);
    }
    public function partner(){
        return $this->hasMany(Partner::class);
    }
    public function otp_requests(){
        return $this->hasMany(OtpRequest::class);
    }
    public function files(){
        return $this->hasMany(File::class);
    }
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'user_categories')->withTimestamps();
    }
    public function user_categories()
    {
        return $this->hasMany(UserCategory::class);
    }
    public function qr_sessions() 
    {
        return $this->hasManyThrough(
            QrSession::class,    // Final related model
            GiftCard::class,    // Intermediate model
            'owner_user_id',          // Foreign key on gift_cards table
            'gift_card_id',   // Foreign key on qr_sessions table
            'id',              //  Local key on users table
            'id'         //  Local key on gift_cards table
        );
    }
    public function gift_cards(){
        return $this->hasMany(GiftCard::class, 'owner_user_id', 'id');
    }
    //Relationship filtering (active gift card, pending qr session)
    public function activeGiftCard(string $giftCardId): ?GiftCard
    {
        return $this->gift_cards()
            ->where('id', $giftCardId)
            ->where('status', 'active')
            ->first();
    }
    public function invoices(){
        return $this->hasMany(Invoice::class);
    }
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
    public function payouts()
    {
        return $this->hasMany(Payout::class);
    }
}
