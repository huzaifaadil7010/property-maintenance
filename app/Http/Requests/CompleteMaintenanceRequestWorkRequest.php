<?php

namespace App\Http\Requests;

use App\Models\MaintenanceRequest;
use Illuminate\Container\Attributes\RouteParameter;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class CompleteMaintenanceRequestWorkRequest extends FormRequest
{
    public function authorize(#[RouteParameter('maintenanceRequest')] MaintenanceRequest $maintenanceRequest): bool
    {
        return $this->user()?->is($maintenanceRequest->assignedTechnician) ?? false;
    }

    public function rules(): array
    {
        return [
            'completion_notes' => ['required', 'string', 'max:1000'],
            'actual_cost' => ['required', 'decimal:0,2', 'min:0'],
            'images' => ['required', 'array', 'min:1'],
            'images.*' => ['string'],
        ];
    }

    public function after(#[RouteParameter('maintenanceRequest')] MaintenanceRequest $maintenanceRequest): array
    {
        return [function (Validator $validator) use ($maintenanceRequest): void {
            if (! $maintenanceRequest->isInProgress()) {
                $validator->errors()->add('cannot_submit', __('Only in-progress jobs can be completed.'));
            }
        }];
    }
}
