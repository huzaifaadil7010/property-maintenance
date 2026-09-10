<?php

namespace App\Http\Resources;

use App\Models\MaintenanceRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TechnicianMaintenanceRequestDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'property' => ['name' => $this->property->name],
            'unit' => ['name' => $this->unit->name],
            'resident' => [
                'name' => $this->resident->name,
                'email' => $this->resident->email,
                'phone' => $this->resident->phone,
            ],
            'category' => [
                'label' => $this->category->getLabel(),
                'value' => $this->category->value,
            ],
            'priority' => [
                'label' => $this->priority->getLabel(),
                'value' => $this->priority->value,
            ],
            'status' => [
                'label' => $this->status->getLabel(),
                'value' => $this->status->value,
            ],
            'completion_notes' => $this->completion_notes,
            'actual_cost' => $this->actual_cost,
            'completed_at' => $this->completed_at,
            'created_at' => $this->created_at,
            'issue_images' => [
                'data' => MaintenanceRequestAttachmentResource::collection(
                    $this->getMedia(MaintenanceRequest::MEDIA_COLLECTION_ISSUE_IMAGES)
                        ->each(fn ($media) => $media->uploader = $this->resident),
                ),
            ],
            'completion_images' => [
                'data' => MaintenanceRequestAttachmentResource::collection(
                    $this->getMedia(MaintenanceRequest::MEDIA_COLLECTION_COMPLETION_IMAGES)
                        ->each(fn ($media) => $media->uploader = $this->assignedTechnician),
                ),
            ],
            'status_logs' => [
                'data' => MaintenanceRequestStatusLogResource::collection($this->statusLogs),
            ],
        ];
    }
}
