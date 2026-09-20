<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class PlanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'unit_amount' => $this->unit_amount,
            'currency' => $this->currency,
            'billing_interval' => $this->billing_interval,
            'billing_interval_count' => $this->billing_interval_count,
            'trial_days' => $this->trial_days,
            'is_featured' => $this->is_featured,
            'features' => collect($this->features)->map(
                fn (mixed $value, string $key): array => [
                    'key' => $key,
                    'label' => Str::headline($key),
                    'value' => $value,
                ],
            )->values(),
        ];
    }
}
