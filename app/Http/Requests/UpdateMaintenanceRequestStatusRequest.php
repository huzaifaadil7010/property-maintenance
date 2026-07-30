<?php

namespace App\Http\Requests;

use App\Enums\MaintenanceRequestStatus;
use App\Models\MaintenanceRequest;
use Illuminate\Container\Attributes\RouteParameter;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateMaintenanceRequestStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(MaintenanceRequestStatus::class)],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function after(#[RouteParameter('maintenanceRequest')] MaintenanceRequest $maintenanceRequest): array
    {
        return [
            function (Validator $validator) use ($maintenanceRequest): void {
                if ($maintenanceRequest->assigned_technician_id === null) {
                    $validator->errors()->add(
                        'cannot_submit',
                        __('Assign a technician before updating the status.'),
                    );
                }
            },
            function (Validator $validator) use ($maintenanceRequest): void {
                if ($this->input('status') === $maintenanceRequest->status?->value) {
                    $validator->errors()->add(
                        'cannot_submit',
                        __('The request already has this status.'),
                    );
                }
            },
        ];
    }
}
