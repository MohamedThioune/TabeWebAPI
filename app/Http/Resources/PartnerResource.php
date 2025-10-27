<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PartnerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        //Context admin
        $context_admin = $request->user()?->can('seeSensitiveData', $this);
        return [
            'name' => $this->name,
            'legal_name' => $this->when($context_admin, $this->legal_name),
            'sector'  => $this->sector,
            'office_phone' => $this->office_phone,

            'payout_method' => $this->when($context_admin, $this->payout_method),
            'payout_account' => $this->when($context_admin, $this->payout_account),

            'kyc_status' => $this->when($context_admin, $this->kyc_status)
        ];
    }
}
