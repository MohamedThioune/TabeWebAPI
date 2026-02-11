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
        $transactions = TransactionResource::collection($this->whenLoaded('transactions'));
        $total_transactions = ($this->resource->transactions()) ? $this->resource->transactions()->count() : 0;
        //Context admin
        $context_admin = $request->user()?->can('seeSensitiveData');
        return [
            'id' => $this->id,
            'gross_amount' => $this->gross_amount,
            'net_amount' => $this->net_amount,
            'commentary' => $this->commentary,
            'fees' => $this->fees,
            'currency' => $this->currency,
            'status' => $this->status,
            'transactions' => $this->when($show_transactions, TransactionResource::collection($this->whenLoaded('transactions'))),
            'total_transactions' => $total_transactions,
            'user' => $this->when($context_admin, new UserResource($this->resource->user)),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
