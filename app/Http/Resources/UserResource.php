<?php

namespace App\Http\Resources;

use App\Domain\Users\ValueObjects\Type;
use App\Http\Requests\API\CustomerAPIRequest;
use App\Http\Requests\API\EnterpriseAPIRequest;
use App\Http\Requests\API\PartnerAPIRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $role = ($this->roles) ? $this->roles->pluck('name')->toArray()[0] : Type::Customer->value;
        $this->load($role);
        $childResource = match ($role) {
            Type::Customer->value   => CustomerResource::collection($this->whenLoaded('customer')),
            Type::Enterprise->value => EnterpriseResource::collection($this->whenLoaded('enterprise')),
            Type::Partner->value    => PartnerResource::collection($this->whenLoaded('partner')),
        };
//        return [$role => $childResource];
        return [
            $role => ($childResource) ? $childResource[0] : null,
            'id' => $this->id,
            'email' => $this->email,
            'phone' => $this->phone,
            'whatsApp' => $this->whatsApp,
            'is_active' => $this->is_active,
            'phone_verified_at' => $this->phone_verified_at
        ];
    }
}
