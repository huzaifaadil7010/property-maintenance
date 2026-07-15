<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ResidentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $activeOccupancy = $this->occupancies->first();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'property_name' => $activeOccupancy?->unit?->property?->name,
            'unit_name' => $activeOccupancy?->unit?->name,
            'move_in_date' => $activeOccupancy?->starts_at,
            'created_at' => $this->created_at,
        ];
    }
}
