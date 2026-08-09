<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PropertyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => [
                'label' => $this->type->getLabel(),
                'value' => $this->type->value,
            ],
            'address' => $this->address,
            'city' => $this->city,
            'created_at' => $this->created_at,
            'units_count' => $this->whenCounted('units'),
            'available_units_count' => $this->available_units_count ?? null,
        ];
    }
}
