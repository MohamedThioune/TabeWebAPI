<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @method static pluck(string $string)
 */
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

        'payout_method', //bank_transfer, mobile_money, wallet
        'payout_account',  //ex: RIB, numéro OM/Wave

        'kyc_status', //active, suspended, pending_approval
         // 'tax_id', //NINEA, registre commerce
        'user_id'
    ];

    public static array $ruleCreated = [
        'name' => 'required|string|max:255',
    ];

    public static array $ruleUpdated = [
        'name' => 'string|max:255',
        'legal_name' => 'string|max:255',
        'sector' => 'string|in:Mode,Beauté,Gastronomie,Technologie,Bien-être,Décoration,Sport,Librairie',
        'office_phone' => 'string|max:255',
        'payout_method' => 'string|in:bank_transfer,mobile_money',
        'payout_account' => 'string|max:255',
//        'kyc_status' => 'string|in:pending,verified,rejected,not_submitted',
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }


}
