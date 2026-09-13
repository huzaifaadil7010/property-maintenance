<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'event' => $this->event?->value,
            'title' => $this->getProperty('title'),
            'description' => $this->description,
            'icon_name' => $this->icon_name,
            'icon_color' => $this->icon_color,
            'icon_bg_color' => $this->icon_bg_color,
            'created_at' => $this->created_at,
        ];
    }
}
