<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Partner extends Model
{
    use HasUuids, HasFactory;

    protected $table = 'partners';

    protected $fillable = [
        'id',
        'name',
        'legal_name',
        'sector', //ex: mode, beauté, gastronomie
        'office_phone',

        'address',
        'city',
        'country',

        'payout_method', //bank_transfer, mobile_money, wallet
        'payout_account',  //ex: RIB, numéro OM/Wave

        'kyc_status', //active, suspended, pending_approval
         // 'tax_id', //NINEA, registre commerce

        'user_id'
    ];

    public static array $ruleCreated = [
        'name' => 'required|string|max:255',
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }
}
