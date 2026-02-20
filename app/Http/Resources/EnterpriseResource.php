<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EnterpriseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'phone' => $this->phone,
            'size' => $this->size,
            'sector' => $this->sector,
            'address' => $this->address,
            // 'employees' => ''
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
