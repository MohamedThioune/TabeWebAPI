<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class OTPRequest extends Model
{
    use HasUuids;

    public $table = 'otp_requests';
    protected $fillable = [
        'id',
        'user_id',
        'channel',
        'identifier',
        'otp_code',
        'purpose',
        'status',
        'attempt_count',
        'expires_at',
    ];

    protected $casts = [];

    public static array $ruleRequest = [
        'purpose' => 'required|string|in:login,reset_password,activate_card,verify_card,others',
        'channel' => 'required|string|in:whatsapp,sms',
    ];

    public static array $ruleVerify = [
        'purpose' => 'required|string|in:login,reset_password,activate_card,verify_card,others',
        'otp_code' => 'required|string|min:6|max:6',
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }
}
