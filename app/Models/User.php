<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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

class User extends Authenticatable
{
    use HasUuids, HasApiTokens, HasRoles, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
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
    ];

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

//    public function getRouteKeyName()
//    {
//        return 'phone';
//    }

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
            'is_phone_verified' => ["boolean"],
            'is_active' => ["boolean"],
            'country' => ["string", "max:255"],
            'city' => ["string", "max:255"],
            'address' => ["string", "max:255"],
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

    public function findForPassport(string $username): User
    {
        $user = $this->where('phone', $username)->first();

        if (! $user)
            throw \Illuminate\Validation\ValidationException::withMessages([
                "phone" => "Phone number not found on records",
            ]);

        return $user;
    }

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
    public function gift_cards(){
        return $this->hasMany(GiftCard::class);
    }
    public function files(){
        return $this->hasMany(File::class);
    }
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'user_categories')
            ->withTimestamps();
    }

}
