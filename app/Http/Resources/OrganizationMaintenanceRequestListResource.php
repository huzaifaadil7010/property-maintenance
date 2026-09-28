<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrganizationMaintenanceRequestListResource extends JsonResource
{
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
            'category' => $this->category->value,
            'priority' => $this->priority->value,
            'status' => $this->status->value,
            'created_at' => $this->created_at,
        ];
    }
}
