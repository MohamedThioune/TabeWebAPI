<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{
    
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        //Load the relations
        $this->load('gift_card');

        return [
            'id' => $this->id,
            'type' => $this->type,
            'amount' => $this->amount,
            'reference_number' => $this->reference_number,
            'status' => $this->status,
            'receipt_url' => $this->receipt_url,
            'endpoint' => $this->endpoint,
            'gift_card' => new GiftCardResource($this->whenLoaded('gift_card')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
