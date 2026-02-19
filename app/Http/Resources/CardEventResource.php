<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CardEventResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $gift_card = ($this->type === 'used') ? $this->giftcard : null;
        return [
            //'id' => $this->id,
            'type' => $this->type,
            'created_at' => $this->created_at,
            'transaction' => $this->when($gift_card, function() use ($gift_card) {
                $transaction = $gift_card->transactions()->where('transactions.status', 'authorized')->first();
                return [
                    'id' => $transaction?->id,
                    'amount' => $transaction?->amount,
                ];
            }),
            // 'updated_at' => $this->updated_at
        ];
    }
}
