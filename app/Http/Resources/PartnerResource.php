<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\UserResource;

class PartnerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->name,
            'legal_name' => $this->legal_name,
            'sector'  => $this->sector,
            'office_phone' => $this->office_phone,
            // 'user' => new UserResource($this->whenLoaded('user')),
            'address' => $this->address,
            'city' => $this->city,
            'country' => $this->country,

            'payout_method' => $this->payout_method,
            'payout_account' => $this->payout_account,

            'kyc_status' => $this->kyc_status
        ];
    }
}
