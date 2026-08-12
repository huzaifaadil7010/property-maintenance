<?php

namespace App\Http\Requests;

use App\Enums\MaintenanceCategory;
use App\Enums\MaintenancePriority;
use App\Enums\OccupancyStatus;
use App\Models\Occupancy;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class CreateMaintenanceRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', Rule::enum(MaintenanceCategory::class)],
            'priority' => ['required', Rule::enum(MaintenancePriority::class)],
            'description' => ['required', 'string'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $hasActiveOccupancy = Occupancy::query()
                    ->where('resident_id', $this->user()?->id)
                    ->where('status', OccupancyStatus::ACTIVE)
                    ->exists();

                if (! $hasActiveOccupancy) {
                    $validator->errors()->add(
                        'cannot_submit',
                        __('You must have an active residence before reporting an issue.'),
                    );
                }
            },
        ];
    }
}
