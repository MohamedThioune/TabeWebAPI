<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class QRSessionResource extends JsonResource
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
            'status' => $this->status,
            'payload' => $this->token,
            'url' => $this->url,
            'expired_at' => $this->expired_at,
            'created_at' => $this->created_at,
             // 'updated_at' => $this->updated_at
        ];
    }
}
