<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = json_decode($this->data);
        return [
            'id' => $this->id,
            'title' => $data->title ?? null,
            'body' => $data->body ?? null,
            'level' => $data->level ?? null,
            'model' => $data->model ?? null,
            'is_read' => $this->is_read ?? null,
            'read_at' => $this->read_at ?? null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
