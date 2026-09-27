<?php

namespace App\Validation;

use App\Enums\MaintenanceRequestStatus;
use App\Enums\PropertyType;
use App\Enums\TechnicianSpecialty;
use App\Enums\UnitStatus;
use App\Models\Property;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Validation\Rule;

class OrganizationInputRules
{
    public static function property(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::enum(PropertyType::class)],
            'address' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
        ];
    }

    public static function unit(int $organizationId, int $propertyId, ?Unit $unit = null): array
    {
        return [
            'property_id' => ['required', 'integer', Rule::exists(Property::class, 'id')->where('organization_id', $organizationId)],
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique(Unit::class, 'name')->ignore($unit)->where('property_id', $propertyId),
            ],
            'floor' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::enum(UnitStatus::class)],
        ];
    }

    public static function resident(int $organizationId, int $propertyId): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique(User::class)],
            'phone' => ['nullable', 'string', 'max:255'],
            'property_id' => ['required', 'integer', Rule::exists(Property::class, 'id')->where('organization_id', $organizationId)],
            'unit_id' => ['required', 'integer', Rule::exists(Unit::class, 'id')->where('organization_id', $organizationId)->where('property_id', $propertyId)],
        ];
    }

    public static function technician(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique(User::class)],
            'phone' => ['nullable', 'string', 'max:255'],
            'specialty' => ['required', Rule::enum(TechnicianSpecialty::class)],
            'is_available' => ['boolean'],
        ];
    }

    public static function assignment(): array
    {
        return [
            'assigned_technician_id' => ['required', 'integer', Rule::exists(User::class, 'id')],
            'notes' => ['nullable', 'string'],
        ];
    }

    public static function maintenanceStatus(): array
    {
        return [
            'status' => ['required', Rule::enum(MaintenanceRequestStatus::class)],
            'notes' => ['nullable', 'string'],
        ];
    }
}
