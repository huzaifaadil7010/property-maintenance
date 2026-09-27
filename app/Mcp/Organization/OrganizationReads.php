<?php

namespace App\Mcp\Organization;

use App\Actions\Organization\Common\GetAvailableTechniciansForDropdown;
use App\Actions\Organization\Common\GetAvailableUnitsForResidentDropdown;
use App\Actions\Organization\Common\GetPropertiesForDropDown;
use App\Actions\Organization\GetActivityLogs;
use App\Actions\Organization\GetMaintenanceRequests;
use App\Actions\Organization\GetProperties;
use App\Actions\Organization\GetResidents;
use App\Actions\Organization\GetTechnicians;
use App\Actions\Organization\GetUnits;
use App\Data\ActivityLogPaginationData;
use App\Data\MaintenanceRequestFilterData;
use App\Data\PropertyFilterData;
use App\Data\ResidentFilterData;
use App\Data\TechnicianFilterData;
use App\Data\UnitFilterData;
use App\Enums\MaintenanceRequestStatus;
use App\Enums\PropertyType;
use App\Enums\TechnicianSpecialty;
use App\Enums\UnitStatus;
use App\Http\Resources\ActivityLogResource;
use App\Http\Resources\MaintenanceRequestResource;
use App\Http\Resources\OrganizationMaintenanceRequestDetailResource;
use App\Http\Resources\PropertyResource;
use App\Http\Resources\ResidentResource;
use App\Http\Resources\TechnicianResource;
use App\Http\Resources\UnitResource;
use App\Models\MaintenanceRequest;
use App\Models\Organization;
use App\Models\Property;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class OrganizationReads
{
    public static function listing(array $input): array
    {
        $data = Validator::make($input, [
            'resource' => ['required', Rule::in(['properties', 'units', 'residents', 'technicians', 'maintenance-requests', 'activity-logs'])],
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::enum(MaintenanceRequestStatus::class)],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', Rule::in([10, 20, 25, 30, 40, 50])],
        ])->validate();

        if (($data['status'] ?? null) !== null && $data['resource'] !== 'maintenance-requests') {
            throw ValidationException::withMessages(['status' => 'Status filtering is only available for maintenance requests.']);
        }

        if (($data['search'] ?? null) !== null && $data['resource'] === 'activity-logs') {
            throw ValidationException::withMessages(['search' => 'Activity logs do not support search.']);
        }

        $filters = [
            'search' => $data['search'] ?? null,
            'page' => $data['page'] ?? null,
            'perPage' => $data['per_page'] ?? null,
        ];

        [$paginator, $resource] = match ($data['resource']) {
            'properties' => [GetProperties::handle(PropertyFilterData::from($filters)), PropertyResource::class],
            'units' => [GetUnits::handle(UnitFilterData::from($filters)), UnitResource::class],
            'residents' => [GetResidents::handle(ResidentFilterData::from($filters)), ResidentResource::class],
            'technicians' => [GetTechnicians::handle(TechnicianFilterData::from($filters)), TechnicianResource::class],
            'maintenance-requests' => [GetMaintenanceRequests::handle(MaintenanceRequestFilterData::from([...$filters, 'status' => $data['status'] ?? null])), MaintenanceRequestResource::class],
            'activity-logs' => [GetActivityLogs::handle(ActivityLogPaginationData::from($filters)), ActivityLogResource::class],
        };

        return self::paginate($paginator, $resource);
    }

    public static function detail(array $input, Organization $organization): array
    {
        $data = Validator::make($input, [
            'resource' => ['required', Rule::in(['properties', 'units', 'residents', 'technicians', 'maintenance-requests'])],
            'id' => ['required', 'integer', 'min:1'],
        ])->validate();

        [$model, $resource] = match ($data['resource']) {
            'properties' => [Property::query()->where('organization_id', $organization->id)->withCount('units')->findOrFail($data['id']), PropertyResource::class],
            'units' => [Unit::query()->where('organization_id', $organization->id)->with('property')->findOrFail($data['id']), UnitResource::class],
            'residents' => [self::member($data['id'], $organization, 'resident')->load(['occupancies' => fn ($query) => $query->active()->with('unit.property')]), ResidentResource::class],
            'technicians' => [self::member($data['id'], $organization, 'technician')->load('technicianProfiles')->loadCount('assignedMaintenanceRequests as assigned_requests_count'), TechnicianResource::class],
            'maintenance-requests' => [MaintenanceRequest::query()->where('organization_id', $organization->id)->with(['property', 'unit', 'resident', 'assignedTechnician', 'media', 'statusLogs.changedBy'])->findOrFail($data['id']), OrganizationMaintenanceRequestDetailResource::class],
        };

        return (new $resource($model))->resolve();
    }

    public static function options(): array
    {
        return [
            'properties' => GetPropertiesForDropDown::handle()->map->only(['id', 'name'])->all(),
            'available_units' => GetAvailableUnitsForResidentDropdown::handle()->map->only(['id', 'property_id', 'name', 'floor', 'status'])->all(),
            'available_technicians' => GetAvailableTechniciansForDropdown::handle()->map->only(['id', 'name'])->all(),
            'property_types' => PropertyType::getLabeledValues(),
            'unit_statuses' => UnitStatus::getLabeledValues(),
            'technician_specialties' => TechnicianSpecialty::getLabeledValues(),
            'maintenance_request_statuses' => MaintenanceRequestStatus::getLabeledValues(),
        ];
    }

    private static function member(int $id, Organization $organization, string $role): User
    {
        return User::query()->role($role)->whereHas('organizations', fn ($query) => $query->where('organizations.id', $organization->id)->where('organization_user.is_active', true))->findOrFail($id);
    }

    private static function paginate(LengthAwarePaginator $paginator, string $resource): array
    {
        return [
            'data' => $paginator->getCollection()->map(fn ($item): array => (new $resource($item))->resolve())->all(),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'last_page' => $paginator->lastPage(),
                'total' => $paginator->total(),
            ],
        ];
    }
}
