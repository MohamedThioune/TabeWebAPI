<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasUuids, HasFactory;

    protected $table = 'customers';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'first_name',
        'last_name',
        'birthdate',
        'gender',
        'address',
        'city',
        'country',
        'preferences',
        'user_id'
    ];

    protected $casts = [
        'preferences' => 'array',
    ];

    public static array $ruleCreated = [
        'first_name' => 'required|string|max:255',
        'last_name' => 'required|string|max:255',
    ];

    public static array $ruleUpdated = [
        'first_name' => 'string|max:255',
        'last_name' => 'string|max:255',
        'gender' => 'string|in:male,female',
        'birthdate' => 'date',
        'address' => 'string|max:255',
        'city' => 'string|max:255',
        'country' => 'string|max:255',
        'preferences' => 'array',
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

}
