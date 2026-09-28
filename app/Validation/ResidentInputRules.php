<?php

namespace App\Validation;

use App\Enums\MaintenanceCategory;
use App\Enums\MaintenancePriority;
use Illuminate\Validation\Rule;

class ResidentInputRules
{
    public static function createMaintenanceRequest(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', Rule::enum(MaintenanceCategory::class)],
            'priority' => ['required', Rule::enum(MaintenancePriority::class)],
            'description' => ['required', 'string'],
            'images' => ['required', 'array', 'min:1', 'max:10'],
            'images.*' => ['required', 'string'],
        ];
    }

    public static function confirmResolutionNotes(): array
    {
        return ['notes' => ['nullable', 'string']];
    }

    public static function reopenNotes(): array
    {
        return ['notes' => ['required', 'string', 'max:1000']];
    }
}
