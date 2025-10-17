<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Enterprise extends Model
{
    use HasUuids;

    protected $table = 'enterprises';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'name',
        'office_phone',
        'kyc_status',
        'user_id',
        // 'tax_id',
        // 'billing_profile',
    ];

    public static array $ruleCreated = [
        'name' => 'required|string|max:255',
    ];

    public static array $ruleUpdated = [
        'name' => 'string|max:255',
        'office_phone' => 'string|max:255',
        'kyc_status' => 'string|in:pending,verified,rejected,not_submitted'
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }
}
