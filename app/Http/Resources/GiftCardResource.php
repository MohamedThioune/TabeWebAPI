<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class GiftCardResource extends JsonResource
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
        $this->load('qrSessions');
        $this->load('user');
        $this->load('beneficiary');
        $this->load('design');
        $this->load('cardevent');
        
        $qrResource = QRSessionResource::collection($this->whenLoaded('qrSessions'));
        $context_admin = $request->user()?->can('seeSensitiveData', $this->resource);

        return [
            'id' => $this->id,
            'code' => $this->code,
            'belonging_type' => $this->belonging_type,
            'type' => $this->type,
            'face_amount' => $this->face_amount,
            'status' => $this->getComputedStatus(),
            'expired_at' => $this->expired_at,
            'issued_via' => $this->issued_via,
            'qr' =>  isset($qrResource[0]) ? $qrResource[0] : null,
            'owner' => new UserResource($this->whenLoaded('user')),
            'beneficiary' => new BeneficiaryResource($this->whenLoaded('beneficiary')),
            'design' => new DesignResource($this->whenLoaded('design')),
            'card_events' => $this->when($context_admin, CardEventResource::collection($this->whenLoaded('cardevent'))),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
