<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EnterpriseResource extends JsonResource
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
            'office_phone' => $this->office_phone,
            'kyc_status' => $this->when($context_admin, $this->kyc_status)
        ];
    }
}
