<?php

namespace App\Http\Requests;

use App\Models\Occupancy;
use App\Validation\ResidentInputRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class CreateMaintenanceRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ResidentInputRules::createMaintenanceRequest();
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $hasActiveOccupancy = Occupancy::query()
                    ->where('resident_id', $this->user()?->id)
                    ->active()
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
