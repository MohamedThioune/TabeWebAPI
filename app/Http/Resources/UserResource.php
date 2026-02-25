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
        $role = $this->roles->pluck('name')->toArray()[0] ?? Type::Customer->value;
        if ($role != Type::Admin->value) $this->load($role);
        $this->load('categories');

        //Child resources part
        $childResources = match ($role) {
            Type::Customer->value   => CustomerResource::collection($this->whenLoaded('customer')),
            Type::Enterprise->value => EnterpriseResource::collection($this->whenLoaded('enterprise')),
            Type::Partner->value    => PartnerResource::collection($this->whenLoaded('partner')),

            default => null,
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

        //Context admin
        $context_admin = $request->user()?->can('seeSensitiveData', $this->resource);
        return [
            $role => $childResource,
            'id' => $this->when($context_admin, $this->id),
            'sigla' => strtoupper($sigla),
            'avatar' => new FileResource($this->files()->where('meaning', 'avatar')->latest('created_at')->first()),
            'banner' => new FileResource($this->files()->where('meaning', 'banner')->latest('created_at')->first()),
            'email' => $this->when($context_admin, $this->email),
            'phone' => $this->phone,
            'whatsApp' => $this->when($context_admin, $this->whatsApp),
            'website' => $this->website,
            'categories' => CategoryResource::collection($this->whenLoaded('categories')),
            'bio' => $this->bio,
            'cards' => $this->when($context_admin && $role === Type::Customer->value, $this->gift_cards()->count()),
            'transactions' => $this->when($context_admin && $role === Type::Partner->value, $this->transactions()->where('status', 'authorized')->sum('amount')),
            'last_reimboursement' => $this->when($context_admin && $role === Type::Partner->value, optional($this->payouts()->where('status', 'completed')->latest('created_at')->first())->created_at?->format('d/m/Y')),
            'rate_success' => $this->when($context_admin && $role === Type::Partner->value, $this->transactions()->where('status', 'captured')->count() > 0 ? round($this->transactions()->where('status', 'captured')->count() / $this->transactions()->count() * 100, 1) . '%' : 'N/A'),
            'country' => $this->country,
            'city' => $this->when($context_admin, $this->city),
            'address' => $this->when($context_admin, $this->address),
            'is_active' => $this->when($context_admin, $this->is_active),
            'last_activity' => $this->when($context_admin, "N/A"),
            'phone_verified_at' => $this->when($context_admin, $this->phone_verified_at),
            'user_registered_at' => $this->when($context_admin, $this->created_at?->format('M Y') ),
        ];
    }
}
