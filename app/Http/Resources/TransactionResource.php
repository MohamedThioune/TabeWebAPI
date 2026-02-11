<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $this->loadMissing('gift_card');
        //Context admin
        $context_admin = $request->user()?->can('seeSensitiveData');
        return [
            'id' => $this->id,
            'status' => $this->status,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'gift_card' => new GiftCardResource($this->whenLoaded('gift_card')),
            'user' => $this->when($context_admin, new UserResource($this->resource->user)),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
