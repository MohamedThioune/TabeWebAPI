<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OptionResource extends JsonResource
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
            'min_amount_card' => $this->min_amount_card,
            'max_amount_card' => $this->max_amount_card,
            'period_validity_card' => $this->period_validity_card,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
