<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\UserResource;

class PayoutResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $show_transactions = (bool)$request->get('show_transactions');
        return [
            'id' => $this->id,
            'gross_amount' => $this->gross_amount,
            'net_amount' => $this->net_amount,
            'fees' => $this->fees,
            'currency' => $this->currency,
            'status' => $this->status,
            'transactions' => $this->when($show_transactions, TransactionResource::collection($this->whenLoaded('transactions'))),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
