<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ResidentMaintenanceRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
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
            'unit' => [
                'id' => $this->unit->id,
                'name' => $this->unit->name,
            ],
            'created_at' => $this->created_at,
        ];
    }
}
