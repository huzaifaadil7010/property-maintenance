<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TechnicianMaintenanceRequestResource extends JsonResource
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
            'created_at' => $this->created_at,
        ];
    }
}
