<?php

namespace App\Mcp\Organization;

use App\Actions\Billing\GetUnitCreationAllowance;
use App\Actions\Organization\MaintenanceRequest\AssignMaintenanceRequestTechnician;
use App\Actions\Organization\MaintenanceRequest\UpdateMaintenanceRequestStatus;
use App\Actions\Organization\Property\CreateProperty;
use App\Actions\Organization\Property\DeleteProperty;
use App\Actions\Organization\Property\UpdateProperty;
use App\Actions\Organization\Resident\CreateResident;
use App\Actions\Organization\Technician\CreateTechnician;
use App\Actions\Organization\Unit\CreateUnit;
use App\Actions\Organization\Unit\DeleteUnit;
use App\Actions\Organization\Unit\UpdateUnit;
use App\Data\AssignMaintenanceRequestTechnicianData;
use App\Data\MaintenanceRequestStatusData;
use App\Data\PropertyData;
use App\Data\ResidentData;
use App\Data\TechnicianData;
use App\Data\UnitData;
use App\Enums\MaintenanceRequestStatus;
use App\Models\MaintenanceRequest;
use App\Models\Occupancy;
use App\Models\Organization;
use App\Models\Property;
use App\Models\Unit;
use App\Models\User;
use App\Validation\OrganizationInputRules;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OrganizationChange
{
    private const int TOKEN_LIFETIME_MINUTES = 5;

    public static function prepare(string $operation, array $input, User $owner, Organization $organization): array
    {
        $data = self::validate($operation, $input, $organization);
        $impact = self::impact($operation, $data, $organization);
        $token = Str::random(64);
        $expiresAt = now()->addMinutes(self::TOKEN_LIFETIME_MINUTES);

        Cache::put(self::cacheKey($token), [
            'owner_id' => $owner->id,
            'organization_id' => $organization->id,
            'operation' => $operation,
            'data' => $data,
            'impact' => $impact,
        ], $expiresAt);

        return [
            'operation' => $operation,
            'summary' => self::summary($operation, $data),
            'impact' => $impact,
            'confirmation_token' => $token,
            'expires_at' => $expiresAt->toIso8601String(),
            'requires_client_approval' => true,
        ];
    }

    public static function confirm(string $token, User $owner, Organization $organization): array
    {
        return Cache::lock('organization-mcp:confirmation-lock:'.$token, 10)->block(3, function () use ($token, $owner, $organization): array {
            $prepared = Cache::pull(self::cacheKey($token));

            if (! is_array($prepared)
                || $prepared['owner_id'] !== $owner->id
                || $prepared['organization_id'] !== $organization->id) {
                throw ValidationException::withMessages(['confirmation_token' => 'This confirmation has expired or was already used. Prepare the change again.']);
            }

            $operation = $prepared['operation'];
            $data = self::validate($operation, $prepared['data'], $organization);

            if (self::impact($operation, $data, $organization) !== $prepared['impact']) {
                throw ValidationException::withMessages(['confirmation_token' => 'The affected records changed. Prepare the change again.']);
            }

            $result = self::execute($operation, $data, $owner, $organization);

            Log::info('Organization MCP change confirmed', [
                'operation' => $operation,
                'owner_id' => $owner->id,
                'organization_id' => $organization->id,
                'result_id' => $result['id'] ?? null,
            ]);

            return ['operation' => $operation, 'result' => $result];
        });
    }

    private static function cacheKey(string $token): string
    {
        return 'organization-mcp:confirmation:'.$token;
    }

    private static function validate(string $operation, array $input, Organization $organization): array
    {
        $rules = match ($operation) {
            'create-property', 'update-property' => [
                ...($operation === 'update-property' ? ['id' => ['required', 'integer']] : []),
                ...OrganizationInputRules::property(),
            ],
            'delete-property' => ['id' => ['required', 'integer']],
            'create-unit', 'update-unit' => [
                ...($operation === 'update-unit' ? ['id' => ['required', 'integer']] : []),
                ...OrganizationInputRules::unit(
                    $organization->id,
                    (int) ($input['property_id'] ?? 0),
                    $operation === 'update-unit' && isset($input['id']) ? self::unit((int) $input['id'], $organization) : null,
                ),
            ],
            'delete-unit' => ['id' => ['required', 'integer']],
            'create-resident' => [
                ...OrganizationInputRules::resident($organization->id, (int) ($input['property_id'] ?? 0)),
            ],
            'create-technician' => OrganizationInputRules::technician(),
            'assign-technician' => [
                'id' => ['required', 'integer'],
                ...OrganizationInputRules::assignment(),
            ],
            'update-maintenance-status' => [
                'id' => ['required', 'integer'],
                ...OrganizationInputRules::maintenanceStatus(),
            ],
            default => throw ValidationException::withMessages(['operation' => 'Unsupported organization operation.']),
        };

        $data = Validator::make($input, $rules)->validate();

        if (in_array($operation, ['update-property', 'delete-property'], true)) {
            self::property($data['id'], $organization);
        }

        if (in_array($operation, ['update-unit', 'delete-unit'], true)) {
            self::unit($data['id'], $organization);
        }

        if (in_array($operation, ['assign-technician', 'update-maintenance-status'], true)) {
            $maintenanceRequest = self::maintenanceRequest($data['id'], $organization);

            if ($operation === 'assign-technician') {
                if ($maintenanceRequest->assigned_technician_id === $data['assigned_technician_id']) {
                    throw ValidationException::withMessages(['cannot_submit' => 'This technician is already assigned to this request.']);
                }

                $available = User::query()->technician()->whereKey($data['assigned_technician_id'])
                    ->whereHas('technicianProfiles', fn ($query) => $query->where('organization_id', $organization->id)->where('is_available', true))
                    ->exists();

                if (! $available) {
                    throw ValidationException::withMessages(['assigned_technician_id' => 'Selected user is not an available technician.']);
                }
            } elseif ($maintenanceRequest->assigned_technician_id === null
                || $maintenanceRequest->status->value === $data['status']) {
                throw ValidationException::withMessages(['cannot_submit' => 'Assign a technician first and choose a different status.']);
            }
        }

        if (in_array($operation, ['create-unit', 'update-unit'], true)) {
            if ($operation === 'create-unit' && GetUnitCreationAllowance::handle($organization)['remaining'] === 0) {
                throw ValidationException::withMessages(['cannot_submit' => 'This organization has used all unit creations for the current billing period.']);
            }
        }

        if ($operation === 'create-resident') {
            $unit = self::unit($data['unit_id'], $organization);

            if ($unit->property_id !== $data['property_id'] || $unit->occupancies()->active()->exists()) {
                throw ValidationException::withMessages(['unit_id' => 'Selected unit is not available for this property.']);
            }
        }

        return $data;
    }

    private static function impact(string $operation, array $data, Organization $organization): array
    {
        if ($operation === 'delete-property') {
            $property = self::property($data['id'], $organization);
            $unitIds = Unit::query()->where('organization_id', $organization->id)->where('property_id', $property->id)->select('id');

            return [
                'target_updated_at' => (string) $property->updated_at,
                'units' => (clone $unitIds)->count(),
                'occupancies' => Occupancy::query()->where('organization_id', $organization->id)->whereIn('unit_id', $unitIds)->count(),
                'maintenance_requests' => MaintenanceRequest::query()->where('organization_id', $organization->id)->where('property_id', $property->id)->count(),
            ];
        }

        if ($operation === 'delete-unit') {
            $unit = self::unit($data['id'], $organization);

            return [
                'target_updated_at' => (string) $unit->updated_at,
                'occupancies' => Occupancy::query()->where('organization_id', $organization->id)->where('unit_id', $unit->id)->count(),
                'maintenance_requests' => MaintenanceRequest::query()->where('organization_id', $organization->id)->where('unit_id', $unit->id)->count(),
            ];
        }

        if (in_array($operation, ['update-property', 'update-unit', 'assign-technician', 'update-maintenance-status'], true)) {
            $target = match ($operation) {
                'update-property' => self::property($data['id'], $organization),
                'update-unit' => self::unit($data['id'], $organization),
                default => self::maintenanceRequest($data['id'], $organization),
            };

            return [
                'target_updated_at' => (string) $target->updated_at,
                ...($operation === 'assign-technician' ? ['sends_assignment_notification' => true] : []),
            ];
        }

        return match ($operation) {
            'create-resident' => ['sends_account_email' => true, 'unit_id' => $data['unit_id']],
            'create-technician' => ['sends_account_email' => true],
            'create-unit' => ['consumes_unit_creation_allowance' => true],
            default => [],
        };
    }

    private static function summary(string $operation, array $data): string
    {
        return match ($operation) {
            'create-property', 'update-property', 'create-unit', 'update-unit', 'create-resident', 'create-technician' => Str::headline($operation).' "'.$data['name'].'"',
            'delete-property', 'delete-unit' => Str::headline($operation).' #'.$data['id'].' and the related records listed in impact',
            'assign-technician' => 'Assign technician #'.$data['assigned_technician_id'].' to maintenance request #'.$data['id'],
            'update-maintenance-status' => 'Set maintenance request #'.$data['id'].' status to '.$data['status'],
            default => $operation,
        };
    }

    private static function execute(string $operation, array $data, User $owner, Organization $organization): array
    {
        $result = match ($operation) {
            'create-property' => CreateProperty::handle(PropertyData::from($data), $owner, $organization),
            'update-property' => UpdateProperty::handle(self::property($data['id'], $organization), PropertyData::from(Arr::except($data, 'id')), $owner, $organization),
            'delete-property' => DeleteProperty::handle(self::property($data['id'], $organization), $owner, $organization),
            'create-unit' => CreateUnit::handle(UnitData::from(['floor' => null, ...$data]), $owner, $organization),
            'update-unit' => UpdateUnit::handle(self::unit($data['id'], $organization), UnitData::from(['floor' => null, ...Arr::except($data, 'id')]), $owner, $organization),
            'delete-unit' => DeleteUnit::handle(self::unit($data['id'], $organization), $owner, $organization),
            'create-resident' => CreateResident::handle(ResidentData::from($data), $organization, $owner),
            'create-technician' => CreateTechnician::handle(TechnicianData::from($data), $organization, $owner),
            'assign-technician' => AssignMaintenanceRequestTechnician::handle(
                self::maintenanceRequest($data['id'], $organization),
                AssignMaintenanceRequestTechnicianData::from([
                    ...$data,
                    'status' => self::maintenanceRequest($data['id'], $organization)->assigned_technician_id === null
                        ? MaintenanceRequestStatus::ASSIGNED
                        : self::maintenanceRequest($data['id'], $organization)->status,
                ]),
                $owner,
                $organization,
            ),
            'update-maintenance-status' => UpdateMaintenanceRequestStatus::handle(
                self::maintenanceRequest($data['id'], $organization),
                MaintenanceRequestStatusData::from($data),
                $owner,
                $organization,
            ),
        };

        return ['success' => (bool) $result, 'id' => $result instanceof User || $result instanceof Property || $result instanceof Unit || $result instanceof MaintenanceRequest ? $result->id : ($data['id'] ?? null)];
    }

    private static function property(int $id, Organization $organization): Property
    {
        return Property::query()->where('organization_id', $organization->id)->findOrFail($id);
    }

    private static function unit(int $id, Organization $organization): Unit
    {
        return Unit::query()->where('organization_id', $organization->id)->findOrFail($id);
    }

    private static function maintenanceRequest(int $id, Organization $organization): MaintenanceRequest
    {
        return MaintenanceRequest::query()->where('organization_id', $organization->id)->findOrFail($id);
    }
}
