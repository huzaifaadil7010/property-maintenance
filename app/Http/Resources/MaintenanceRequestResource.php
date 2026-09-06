<?php

namespace App\Http\Resources;

use App\Models\MaintenanceRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MaintenanceRequestResource extends JsonResource
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
            'title' => $this->title,
            'description' => $this->description,
            'property' => [
                'id' => $this->property->id,
                'name' => $this->property->name,
            ],
            'unit' => [
                'id' => $this->unit->id,
                'name' => $this->unit->name,
            ],
            'resident' => [
                'id' => $this->resident->id,
                'name' => $this->resident->name,
                'email' => $this->resident->email,
                'phone' => $this->resident->phone,
            ],
            'assigned_technician' => $this->assignedTechnician
                ? [
                    'id' => $this->assignedTechnician->id,
                    'name' => $this->assignedTechnician->name,
                    'email' => $this->assignedTechnician->email,
                    'phone' => $this->assignedTechnician->phone,
                ]
                : null,
            'category' => [
                'label' => str($this->category->value)->headline()->toString(),
                'value' => $this->category->value,
            ],
            'priority' => [
                'label' => str($this->priority->value)->headline()->toString(),
                'value' => $this->priority->value,
            ],
            'status' => [
                'label' => str($this->status->value)->headline()->toString(),
                'value' => $this->status->value,
            ],
            'completion_notes' => $this->completion_notes,
            'actual_cost' => $this->actual_cost,
            'completed_at' => $this->completed_at,
            'closed_at' => $this->closed_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'attachments' => MaintenanceRequestAttachmentResource::collection(
                $this->getMedia(MaintenanceRequest::MEDIA_COLLECTION_ISSUE_IMAGES)
                    ->each(fn ($media) => $media->uploader = $this->resident)
                    ->concat(
                        $this->getMedia(MaintenanceRequest::MEDIA_COLLECTION_COMPLETION_IMAGES)
                            ->each(fn ($media) => $media->uploader = $this->assignedTechnician),
                    ),
            ),
            'status_logs' => MaintenanceRequestStatusLogResource::collection($this->statusLogs),
        ];
    }
}
