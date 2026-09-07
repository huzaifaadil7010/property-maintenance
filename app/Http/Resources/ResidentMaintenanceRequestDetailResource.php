<?php

namespace App\Http\Resources;

use App\Models\MaintenanceRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ResidentMaintenanceRequestDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'property' => ['name' => $this->property->name],
            'unit' => ['name' => $this->unit->name],
            'assigned_technician' => $this->assignedTechnician ? ['name' => $this->assignedTechnician->name] : null,
            'category' => ['label' => str($this->category->value)->headline()->toString(), 'value' => $this->category->value],
            'priority' => ['label' => str($this->priority->value)->headline()->toString(), 'value' => $this->priority->value],
            'status' => ['label' => str($this->status->value)->headline()->toString(), 'value' => $this->status->value],
            'completion_notes' => $this->completion_notes,
            'completed_at' => $this->completed_at,
            'closed_at' => $this->closed_at,
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
