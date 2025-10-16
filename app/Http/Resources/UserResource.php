<?php

namespace App\Http\Resources;

use App\Domain\Users\ValueObjects\Type;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        //Load relation
//        if(isset($this->roles->pluck('name')->toArray()[0]))

        $role = $this->roles->pluck('name')->toArray()[0] ?? Type::Customer->value;
        $this->load($role);
        $this->load('categories');


        //Child resources part
        $childResources = match ($role) {
            Type::Customer->value   => CustomerResource::collection($this->whenLoaded('customer')),
            Type::Enterprise->value => EnterpriseResource::collection($this->whenLoaded('enterprise')),
            Type::Partner->value    => PartnerResource::collection($this->whenLoaded('partner')),
        };
        $childResource = isset($childResources[0]) ? $childResources[0] : null;
        $sigla = null;
        if($childResource):
            if($role == Type::Customer->value ):
                $sigla = ($childResource->first_name) ? $childResource->first_name[0] : '';
                $sigla .= ($childResource->last_name) ?  ' ' . $childResource->last_name[0] : '';
            else:
                $full = explode(' ', $childResource->name, 2);
                $sigla = isset($full[0]) ? substr($full[0], 0, 1) : '';
                $sigla .= isset($full[1]) ?  ' ' . substr($full[1], 0, 1) : '';
            endif;
        endif;

        return [
            $role => $childResource,
            'id' => $this->id,
            'sigla' => strtoupper($sigla),
            'avatar' => new FileResource($this->files->where('meaning', 'avatar')->first()),
            'banner' => new FileResource($this->files->where('meaning', 'banner')->first()),
            'email' => $this->email,
            'phone' => $this->phone,
            'whatsApp' => $this->whatsApp,
            'categories' => CategoryResource::collection($this->whenLoaded('categories')),
            'bio' => $this->bio,
            'country' => $this->country,
            'city' => $this->city,
            'address' => $this->address,
            'is_active' => $this->is_active,
            'phone_verified_at' => $this->phone_verified_at,
            'user_registered_at' => $this->created_at->format('M Y'),
        ];
    }
}
