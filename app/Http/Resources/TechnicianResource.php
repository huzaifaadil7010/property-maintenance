<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TechnicianResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $technicianProfile = $this->technicianProfiles->first();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $technicianProfile?->phone ?? $this->phone,
            'specialty' => $technicianProfile?->specialty
                ? [
                    'label' => $technicianProfile->specialty->getLabel(),
                    'value' => $technicianProfile->specialty->value,
                ]
                : null,
            'is_available' => $technicianProfile?->is_available ?? false,
        ];
    }
}
