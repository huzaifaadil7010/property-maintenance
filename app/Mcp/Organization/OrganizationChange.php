<?php

namespace App\Mcp\Organization;

use App\Actions\Billing\GetUnitCreationAllowance;
use App\Actions\Organization\MaintenanceRequest\AssignMaintenanceRequestTechnician;
use App\Actions\Organization\MaintenanceRequest\UpdateMaintenanceRequestStatus;
use App\Actions\Organization\Property\CreateProperty;
use App\Actions\Organization\Property\DeleteProperty;
use App\Actions\Organization\Property\UpdateProperty;
use App\Actions\Organization\Resident\CreateResident;
use App\Actions\Organization\Resident\UpdateResident;
use App\Actions\Organization\Technician\CreateTechnician;
use App\Actions\Organization\Unit\CreateUnit;
use App\Actions\Organization\Unit\DeleteUnit;
use App\Actions\Organization\Unit\UpdateUnit;
use App\Data\AssignMaintenanceRequestTechnicianData;
use App\Data\MaintenanceRequestStatusData;
use App\Data\OrganizationChangeInputData;
use App\Data\PropertyData;
use App\Data\ResidentData;
use App\Data\TechnicianData;
use App\Data\UnitData;
use App\Enums\MaintenanceRequestStatus;
use App\Enums\OrganizationChangeOperationEnum;
use App\Models\MaintenanceRequest;
use App\Models\Occupancy;
use App\Models\Organization;
use App\Models\Property;
use App\Models\Unit;
use App\Models\User;
use App\Validation\OrganizationInputRules;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OrganizationChange
{
    private const int TOKEN_LIFETIME_MINUTES = 5;

    public static function prepare(OrganizationChangeOperationEnum $operation, OrganizationChangeInputData $input, User $owner, Organization $organization): array
    {
        if (! $operation->accepts($input)) {
            throw ValidationException::withMessages(['operation' => 'The organization change input does not match the selected operation.']);
        }

        self::validate($operation, $input, $organization);

        $impact = self::impact($operation, $input, $organization);
        $token = Str::random(64);
        $expiresAt = now()->addMinutes(self::TOKEN_LIFETIME_MINUTES);

        Cache::put(self::cacheKey($token), [
            'owner_id' => $owner->id,
            'organization_id' => $organization->id,
            'operation' => $operation->value,
            'data' => $input->toArray(),
            'impact' => $impact,
        ], $expiresAt);

        return [
            'operation' => $operation->value,
            'summary' => self::summary($operation, $input),
            'impact' => $impact,
            'confirmation_token' => $token,
            'expires_at' => $expiresAt->toIso8601String(),
            'expires_in_seconds' => self::TOKEN_LIFETIME_MINUTES * 60,
            'requires_client_approval' => true,
        ];
    }

    public static function confirm(string $token, User $owner, Organization $organization): array
    {
        return Cache::lock('organization-mcp:confirmation-lock:'.$token, 10)->block(3, function () use ($token, $owner, $organization): array {
            $prepared = Cache::pull(self::cacheKey($token));

            if (! is_array($prepared)
                || $prepared['owner_id'] !== $owner->id
                || $prepared['organization_id'] !== $organization->id
                || ! is_array($prepared['data'] ?? null)) {
                throw ValidationException::withMessages(['confirmation_token' => 'This confirmation has expired or was already used. Prepare the change again.']);
            }

            $operation = OrganizationChangeOperationEnum::tryFrom($prepared['operation'] ?? '');

            if ($operation === null) {
                throw ValidationException::withMessages(['confirmation_token' => 'This confirmation is invalid. Prepare the change again.']);
            }

            $inputDataClass = $operation->inputDataClass();
            $input = $inputDataClass::validateAndCreate($prepared['data']);

            if (! $operation->accepts($input)) {
                throw ValidationException::withMessages(['confirmation_token' => 'This confirmation is invalid. Prepare the change again.']);
            }

            self::validate($operation, $input, $organization);

            if (self::impact($operation, $input, $organization) !== $prepared['impact']) {
                throw ValidationException::withMessages(['confirmation_token' => 'The affected records changed. Prepare the change again.']);
            }

            $result = self::execute($operation, $input, $owner, $organization);

            Log::info('Organization MCP change confirmed', [
                'operation' => $operation->value,
                'owner_id' => $owner->id,
                'organization_id' => $organization->id,
                'result_id' => $result['id'] ?? null,
            ]);

            return ['operation' => $operation->value, 'result' => $result];
        });
    }

    private static function cacheKey(string $token): string
    {
        return 'organization-mcp:confirmation:'.$token;
    }

    private static function validate(OrganizationChangeOperationEnum $operation, OrganizationChangeInputData $input, Organization $organization): void
    {
        $rules = match ($operation) {
            OrganizationChangeOperationEnum::CREATE_PROPERTY, OrganizationChangeOperationEnum::UPDATE_PROPERTY => [
                ...($operation === OrganizationChangeOperationEnum::UPDATE_PROPERTY ? ['id' => ['required', 'integer']] : []),
                ...OrganizationInputRules::property(),
            ],
            OrganizationChangeOperationEnum::DELETE_PROPERTY => ['id' => ['required', 'integer']],
            OrganizationChangeOperationEnum::CREATE_UNIT, OrganizationChangeOperationEnum::UPDATE_UNIT => [
                ...($operation === OrganizationChangeOperationEnum::UPDATE_UNIT ? ['id' => ['required', 'integer']] : []),
                ...OrganizationInputRules::unit(
                    $organization->id,
                    $input->property_id,
                    $operation === OrganizationChangeOperationEnum::UPDATE_UNIT ? self::unit($input->id, $organization) : null,
                ),
            ],
            OrganizationChangeOperationEnum::DELETE_UNIT => ['id' => ['required', 'integer']],
            OrganizationChangeOperationEnum::CREATE_RESIDENT => [
                ...OrganizationInputRules::resident($organization->id, $input->property_id),
            ],
            OrganizationChangeOperationEnum::UPDATE_RESIDENT => [
                'id' => ['required', 'integer'],
                ...OrganizationInputRules::resident($organization->id, $input->property_id, self::resident($input->id, $organization)),
            ],
            OrganizationChangeOperationEnum::CREATE_TECHNICIAN => OrganizationInputRules::technician(),
            OrganizationChangeOperationEnum::ASSIGN_TECHNICIAN => [
                'id' => ['required', 'integer'],
                ...OrganizationInputRules::assignment(),
            ],
            OrganizationChangeOperationEnum::UPDATE_MAINTENANCE_STATUS => [
                'id' => ['required', 'integer'],
                ...OrganizationInputRules::maintenanceStatus(),
            ],
        };

        Validator::make($input->toArray(), $rules)->validate();

        if (in_array($operation, [OrganizationChangeOperationEnum::UPDATE_PROPERTY, OrganizationChangeOperationEnum::DELETE_PROPERTY], true)) {
            self::property($input->id, $organization);
        }

        if (in_array($operation, [OrganizationChangeOperationEnum::UPDATE_UNIT, OrganizationChangeOperationEnum::DELETE_UNIT], true)) {
            self::unit($input->id, $organization);
        }

        if (in_array($operation, [OrganizationChangeOperationEnum::ASSIGN_TECHNICIAN, OrganizationChangeOperationEnum::UPDATE_MAINTENANCE_STATUS], true)) {
            $maintenanceRequest = self::maintenanceRequest($input->id, $organization);

            if ($operation === OrganizationChangeOperationEnum::ASSIGN_TECHNICIAN) {
                if ($maintenanceRequest->assigned_technician_id === $input->assigned_technician_id) {
                    throw ValidationException::withMessages(['cannot_submit' => 'This technician is already assigned to this request.']);
                }

                $available = User::query()->technician()->whereKey($input->assigned_technician_id)
                    ->whereHas('technicianProfiles', fn ($query) => $query->where('organization_id', $organization->id)->where('is_available', true))
                    ->exists();

                if (! $available) {
                    throw ValidationException::withMessages(['assigned_technician_id' => 'Selected user is not an available technician.']);
                }
            } elseif ($maintenanceRequest->assigned_technician_id === null
                || $maintenanceRequest->status === $input->status) {
                throw ValidationException::withMessages(['cannot_submit' => 'Assign a technician first and choose a different status.']);
            }
        }

        if (in_array($operation, [OrganizationChangeOperationEnum::CREATE_UNIT, OrganizationChangeOperationEnum::UPDATE_UNIT], true)) {
            if ($operation === OrganizationChangeOperationEnum::CREATE_UNIT && GetUnitCreationAllowance::handle($organization)['remaining'] === 0) {
                throw ValidationException::withMessages(['cannot_submit' => 'This organization has used all unit creations for the current billing period.']);
            }
        }

        if ($operation === OrganizationChangeOperationEnum::CREATE_RESIDENT) {
            $unit = self::unit($input->unit_id, $organization);

            if ($unit->property_id !== $input->property_id || $unit->occupancies()->active()->exists()) {
                throw ValidationException::withMessages(['unit_id' => 'Selected unit is not available for this property.']);
            }
        }

        if ($operation === OrganizationChangeOperationEnum::UPDATE_RESIDENT) {
            $resident = self::resident($input->id, $organization);
            $unit = self::unit($input->unit_id, $organization);
            $currentOccupancies = $resident->occupancies()->active()->get();

            if ($currentOccupancies->count() > 1) {
                throw ValidationException::withMessages(['cannot_submit' => 'This resident has multiple active occupancies. Resolve them before editing.']);
            }

            if ($unit->property_id !== $input->property_id
                || $unit->occupancies()->active()->where('resident_id', '!=', $resident->id)->exists()) {
                throw ValidationException::withMessages(['unit_id' => 'Selected unit is not available for this property.']);
            }
        }
    }

    private static function impact(OrganizationChangeOperationEnum $operation, OrganizationChangeInputData $input, Organization $organization): array
    {
        if ($operation === OrganizationChangeOperationEnum::UPDATE_RESIDENT) {
            $resident = self::resident($input->id, $organization);
            $occupancy = $resident->occupancies()->active()->with('unit')->first();
            $unit = self::unit($input->unit_id, $organization);

            return [
                'resident_id' => $resident->id,
                'from_name' => $resident->name,
                'from_email' => $resident->email,
                'from_phone' => $resident->phone,
                'resident_updated_at' => (string) $resident->updated_at,
                'current_occupancy_id' => $occupancy?->id,
                'current_occupancy_updated_at' => $occupancy?->updated_at?->toIso8601String(),
                'from_property_id' => $occupancy?->unit?->property_id,
                'from_unit_id' => $occupancy?->unit_id,
                'to_name' => $input->name,
                'to_email' => $input->email,
                'to_phone' => $input->phone,
                'to_property_id' => $input->property_id,
                'to_unit_id' => $unit->id,
                'target_unit_status' => $unit->status->value,
                'target_unit_updated_at' => (string) $unit->updated_at,
            ];
        }

        if ($operation === OrganizationChangeOperationEnum::DELETE_PROPERTY) {
            $property = self::property($input->id, $organization);
            $unitIds = Unit::query()->where('organization_id', $organization->id)->where('property_id', $property->id)->select('id');

            return [
                'target_updated_at' => (string) $property->updated_at,
                'units' => (clone $unitIds)->count(),
                'occupancies' => Occupancy::query()->where('organization_id', $organization->id)->whereIn('unit_id', $unitIds)->count(),
                'maintenance_requests' => MaintenanceRequest::query()->where('organization_id', $organization->id)->where('property_id', $property->id)->count(),
            ];
        }

        if ($operation === OrganizationChangeOperationEnum::DELETE_UNIT) {
            $unit = self::unit($input->id, $organization);

            return [
                'target_updated_at' => (string) $unit->updated_at,
                'occupancies' => Occupancy::query()->where('organization_id', $organization->id)->where('unit_id', $unit->id)->count(),
                'maintenance_requests' => MaintenanceRequest::query()->where('organization_id', $organization->id)->where('unit_id', $unit->id)->count(),
            ];
        }

        if (in_array($operation, [OrganizationChangeOperationEnum::UPDATE_PROPERTY, OrganizationChangeOperationEnum::UPDATE_UNIT, OrganizationChangeOperationEnum::ASSIGN_TECHNICIAN, OrganizationChangeOperationEnum::UPDATE_MAINTENANCE_STATUS], true)) {
            $target = match ($operation) {
                OrganizationChangeOperationEnum::UPDATE_PROPERTY => self::property($input->id, $organization),
                OrganizationChangeOperationEnum::UPDATE_UNIT => self::unit($input->id, $organization),
                default => self::maintenanceRequest($input->id, $organization),
            };

            return [
                'target_updated_at' => (string) $target->updated_at,
                ...($operation === OrganizationChangeOperationEnum::ASSIGN_TECHNICIAN ? ['sends_assignment_notification' => true] : []),
            ];
        }

        return match ($operation) {
            OrganizationChangeOperationEnum::CREATE_RESIDENT => ['sends_account_email' => true, 'unit_id' => $input->unit_id],
            OrganizationChangeOperationEnum::CREATE_TECHNICIAN => ['sends_account_email' => true],
            OrganizationChangeOperationEnum::CREATE_UNIT => ['consumes_unit_creation_allowance' => true],
            default => [],
        };
    }

    private static function summary(OrganizationChangeOperationEnum $operation, OrganizationChangeInputData $input): string
    {
        return match ($operation) {
            OrganizationChangeOperationEnum::CREATE_PROPERTY,
            OrganizationChangeOperationEnum::UPDATE_PROPERTY,
            OrganizationChangeOperationEnum::CREATE_UNIT,
            OrganizationChangeOperationEnum::UPDATE_UNIT,
            OrganizationChangeOperationEnum::CREATE_RESIDENT,
            OrganizationChangeOperationEnum::UPDATE_RESIDENT,
            OrganizationChangeOperationEnum::CREATE_TECHNICIAN => Str::headline($operation->value).' "'.$input->name.'"',
            OrganizationChangeOperationEnum::DELETE_PROPERTY,
            OrganizationChangeOperationEnum::DELETE_UNIT => Str::headline($operation->value).' #'.$input->id.' and the related records listed in impact',
            OrganizationChangeOperationEnum::ASSIGN_TECHNICIAN => 'Assign technician #'.$input->assigned_technician_id.' to maintenance request #'.$input->id,
            OrganizationChangeOperationEnum::UPDATE_MAINTENANCE_STATUS => 'Set maintenance request #'.$input->id.' status to '.$input->status->value,
        };
    }

    private static function execute(OrganizationChangeOperationEnum $operation, OrganizationChangeInputData $input, User $owner, Organization $organization): array
    {
        $result = match ($operation) {
            OrganizationChangeOperationEnum::CREATE_PROPERTY => CreateProperty::handle(new PropertyData($input->name, $input->type, $input->address, $input->city), $owner, $organization),
            OrganizationChangeOperationEnum::UPDATE_PROPERTY => UpdateProperty::handle(self::property($input->id, $organization), new PropertyData($input->name, $input->type, $input->address, $input->city), $owner, $organization),
            OrganizationChangeOperationEnum::DELETE_PROPERTY => DeleteProperty::handle(self::property($input->id, $organization), $owner, $organization),
            OrganizationChangeOperationEnum::CREATE_UNIT => CreateUnit::handle(new UnitData($input->property_id, $input->name, $input->floor, $input->status), $owner, $organization),
            OrganizationChangeOperationEnum::UPDATE_UNIT => UpdateUnit::handle(self::unit($input->id, $organization), new UnitData($input->property_id, $input->name, $input->floor, $input->status), $owner, $organization),
            OrganizationChangeOperationEnum::DELETE_UNIT => DeleteUnit::handle(self::unit($input->id, $organization), $owner, $organization),
            OrganizationChangeOperationEnum::CREATE_RESIDENT => CreateResident::handle(new ResidentData($input->name, $input->email, $input->property_id, $input->unit_id, $input->phone), $organization, $owner),
            OrganizationChangeOperationEnum::UPDATE_RESIDENT => UpdateResident::handle(new ResidentData($input->name, $input->email, $input->property_id, $input->unit_id, $input->phone), self::resident($input->id, $organization), $owner, $organization),
            OrganizationChangeOperationEnum::CREATE_TECHNICIAN => CreateTechnician::handle(new TechnicianData($input->name, $input->email, $input->specialty, $input->is_available, $input->phone), $organization, $owner),
            OrganizationChangeOperationEnum::ASSIGN_TECHNICIAN => AssignMaintenanceRequestTechnician::handle(
                self::maintenanceRequest($input->id, $organization),
                new AssignMaintenanceRequestTechnicianData(
                    $input->assigned_technician_id,
                    self::maintenanceRequest($input->id, $organization)->assigned_technician_id === null
                        ? MaintenanceRequestStatus::ASSIGNED
                        : self::maintenanceRequest($input->id, $organization)->status,
                    $input->notes,
                ),
                $owner,
                $organization,
            ),
            OrganizationChangeOperationEnum::UPDATE_MAINTENANCE_STATUS => UpdateMaintenanceRequestStatus::handle(
                self::maintenanceRequest($input->id, $organization),
                new MaintenanceRequestStatusData($input->status, $input->notes),
                $owner,
                $organization,
            ),
        };

        $resultId = $result instanceof User || $result instanceof Property || $result instanceof Unit || $result instanceof MaintenanceRequest
            ? $result->id
            : match ($operation) {
                OrganizationChangeOperationEnum::UPDATE_PROPERTY,
                OrganizationChangeOperationEnum::DELETE_PROPERTY,
                OrganizationChangeOperationEnum::UPDATE_UNIT,
                OrganizationChangeOperationEnum::DELETE_UNIT,
                OrganizationChangeOperationEnum::ASSIGN_TECHNICIAN,
                OrganizationChangeOperationEnum::UPDATE_MAINTENANCE_STATUS => $input->id,
                default => null,
            };

        return ['success' => (bool) $result, 'id' => $resultId];
    }

    private static function property(int $id, Organization $organization): Property
    {
        return Property::query()->where('organization_id', $organization->id)->findOrFail($id);
    }

    private static function unit(int $id, Organization $organization): Unit
    {
        return Unit::query()->where('organization_id', $organization->id)->findOrFail($id);
    }

    private static function resident(int $id, Organization $organization): User
    {
        return User::query()->resident()
            ->whereHas('organizations', fn ($query) => $query
                ->where('organizations.id', $organization->id)
                ->where('organization_user.is_active', true))
            ->findOrFail($id);
    }

    private static function maintenanceRequest(int $id, Organization $organization): MaintenanceRequest
    {
        return MaintenanceRequest::query()->where('organization_id', $organization->id)->findOrFail($id);
    }
}
