<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MaintenanceRequestAttachmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'file_path' => $this->getUrl(),
            'srcset' => $this->hasResponsiveImages() ? $this->getSrcset() : null,
            'original_name' => $this->file_name,
            'mime_type' => $this->mime_type,
            'size' => $this->size,
            'uploader' => [
                'id' => $this->uploader->id,
                'name' => $this->uploader->name,
            ],
            'created_at' => $this->created_at,
        ];
    }
}
