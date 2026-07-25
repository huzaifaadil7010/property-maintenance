<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MaintenanceRequestStatusLogResource extends JsonResource
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
            'from_status' => $this->from_status === null ? null : [
                'label' => str($this->from_status->value)->headline()->toString(),
                'value' => $this->from_status->value,
            ],
            'to_status' => $this->to_status === null ? null : [
                'label' => str($this->to_status->value)->headline()->toString(),
                'value' => $this->to_status->value,
            ],
            'notes' => $this->notes,
            'changed_by' => [
                'id' => $this->changedBy->id,
                'name' => $this->changedBy->name,
            ],
            'created_at' => $this->created_at,
        ];
    }
}
