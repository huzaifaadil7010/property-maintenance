<?php

namespace App\Http\Resources;

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
            ],
            'assigned_technician' => $this->assignedTechnician
                ? [
                    'id' => $this->assignedTechnician->id,
                    'name' => $this->assignedTechnician->name,
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
            'created_at' => $this->created_at,
        ];
    }
}
