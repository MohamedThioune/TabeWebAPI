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
        'is_active',
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
        // 'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
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

    public function findForPassport(string $username): User
    {
        return $this->where('phone', $username)->first();
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
}
