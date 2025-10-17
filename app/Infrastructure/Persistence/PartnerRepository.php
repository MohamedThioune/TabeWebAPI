<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Users\Entities\User;
use App\Models\Partner;
use App\Repositories\BaseRepository;

class PartnerRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'id',
        'name',
        'legal_name',
        'sector', //ex: mode, beauté, gastronomie
        'office_phone',
        'payout_method', //bank_transfer, mobile_money, wallet
        'kyc_status', //active, suspended, pending_approval
        'user_id'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return Partner::class;
    }

    public function save(User $user): Partner
    {
        $model = Partner::create([
            'id' => $user->getPartnerId(),
            'name' => $user->getName(),
            'user_id' => $user->getId(),
        ]);

        return $model;
    }
}
