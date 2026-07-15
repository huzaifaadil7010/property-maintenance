<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UnitResource extends JsonResource
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
            'name' => $this->name,
            'property' => [
                'id' => $this->property->id,
                'name' => $this->property->name,
            ],
            'floor' => $this->floor,
            'status' => [
                'label' => $this->status->getLabel(),
                'value' => $this->status->value,
            ],
            'created_at' => $this->created_at,
        ];
    }
}
