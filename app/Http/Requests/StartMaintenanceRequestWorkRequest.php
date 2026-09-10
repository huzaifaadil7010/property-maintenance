<?php

namespace App\Http\Requests;

use App\Models\MaintenanceRequest;
use Illuminate\Container\Attributes\RouteParameter;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StartMaintenanceRequestWorkRequest extends FormRequest
{
    public function authorize(#[RouteParameter('maintenanceRequest')] MaintenanceRequest $maintenanceRequest): bool
    {
        return $this->user()?->is($maintenanceRequest->assignedTechnician) ?? false;
    }

    public function rules(): array
    {
        return [];
    }

    public function after(#[RouteParameter('maintenanceRequest')] MaintenanceRequest $maintenanceRequest): array
    {
        return [function (Validator $validator) use ($maintenanceRequest): void {
            if (! $maintenanceRequest->isAssigned()) {
                $validator->errors()->add('cannot_submit', __('Only assigned jobs can be started.'));
            }
        }];
    }
}
