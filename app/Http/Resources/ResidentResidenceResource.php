<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ResidentResidenceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'property' => [
                'id' => $this->unit->property->id,
                'name' => $this->unit->property->name,
            ],
            'unit' => [
                'id' => $this->unit->id,
                'name' => $this->unit->name,
            ],
        ];
    }
}
