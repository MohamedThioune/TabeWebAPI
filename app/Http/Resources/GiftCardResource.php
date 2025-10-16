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
        $this->load('qrsessions');
        $this->load('user');
        $this->load('beneficiary');
        $this->load('design');

        $qrResource = QRSessionResource::collection($this->whenLoaded('qrsessions'));

        return [
            'id' => $this->id,
            'belonging_type' => $this->belonging_type,
            'face_amount' => $this->face_amount,
            'pin_mask' => $this->pin_mask,
            'is_active' => $this->is_active,
            'expired_at' => $this->expired_at,
            'qr' =>  isset($qrResource[0]) ? $qrResource[0] : null,
            'owner' => new UserResource($this->whenLoaded('user')),
            'beneficiary' => new BeneficiaryResource($this->whenLoaded('beneficiary')),
            'design' => new DesignResource($this->whenLoaded('design')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
