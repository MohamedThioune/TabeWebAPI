<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class FileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $key = Storage::disk('s3')->url($this->path . $this->id);
        $temporaryUrl = Storage::disk('s3')->temporaryUrl(
            $this->path . $this->id,
            now()->addHours(2)
        );
        //Context admin
        $context_admin = $request->user()?->can('seeSensitiveData', $this);
        $context_super_admin = $request->user()?->can('seeSensitiveData');
        return [
            'id' => $this->when($context_admin, $this->id),
            'type' => $this->type,
            'path' => $this->when($context_super_admin, $this->path),
            'key' => $this->when($context_super_admin, $key),
            'temporary_url' => $temporaryUrl,
            'meaning' => $this->when($context_super_admin, $this->meaning),
            'description' => $this->when($context_admin, $this->description),
            'created_at' => $this->when($context_admin, $this->created_at),
            'updated_at' => $this->when($context_admin, $this->updated_at)
        ];
    }
}
