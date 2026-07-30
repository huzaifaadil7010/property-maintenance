<?php

namespace App\Http\Requests;

use App\Models\MaintenanceRequest;
use App\Models\User;
use Illuminate\Container\Attributes\RouteParameter;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class AssignMaintenanceRequestTechnicianRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'assigned_technician_id' => ['required', 'integer', Rule::exists('users', 'id')],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function after(#[RouteParameter('maintenanceRequest')] MaintenanceRequest $maintenanceRequest): array
    {
        return [
            function (Validator $validator) use ($maintenanceRequest): void {
                if ($maintenanceRequest->assigned_technician_id === $this->integer('assigned_technician_id')) {
                    $validator->errors()->add(
                        'cannot_submit',
                        __('This technician is already assigned to this request.'),
                    );
                }
            },
            function (Validator $validator): void {
                $technicianId = $this->integer('assigned_technician_id');

                $isAvailableTechnician = User::query()
                    ->technician()
                    ->whereRelation('technicianProfiles', 'is_available', true)
                    ->whereKey($technicianId)
                    ->exists();

                if (! $isAvailableTechnician) {
                    $validator->errors()->add(
                        'assigned_technician_id',
                        __('Selected user is not an available technician.'),
                    );
                }
            },
        ];
    }
}
