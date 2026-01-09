<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PayoutLineResource extends JsonResource
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
            'amount' => $this->amount,
            'transaction_id' => $this->transaction_id,
            'payout_id' => $this->payout_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
