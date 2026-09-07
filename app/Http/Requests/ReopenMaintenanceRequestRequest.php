<?php

namespace App\Http\Requests;

use App\Enums\MaintenanceRequestStatus;
use App\Models\MaintenanceRequest;
use Illuminate\Container\Attributes\RouteParameter;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ReopenMaintenanceRequestRequest extends FormRequest
{
    public function authorize(#[RouteParameter('maintenanceRequest')] MaintenanceRequest $maintenanceRequest): bool
    {
        return $this->user()?->is($maintenanceRequest->resident) ?? false;
    }

    public function rules(): array
    {
        return ['status' => ['required', Rule::enum(MaintenanceRequestStatus::class), Rule::in([MaintenanceRequestStatus::REOPENED->value])], 'notes' => ['required', 'string', 'max:1000']];
    }

    public function after(#[RouteParameter('maintenanceRequest')] MaintenanceRequest $maintenanceRequest): array
    {
        return [function (Validator $validator) use ($maintenanceRequest): void {
            if (! $maintenanceRequest->isCompleted()) {
                $validator->errors()->add('cannot_submit', __('Only completed requests can be reopened.'));
            }
        }];
    }
}
