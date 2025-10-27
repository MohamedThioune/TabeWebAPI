<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
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
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'birthdate' =>  $this->birthdate,
            'gender' => $this->when($context_admin, $this->gender),

            'preferences' => $this->when($context_admin, $this->preferences),
        ];
    }
}
