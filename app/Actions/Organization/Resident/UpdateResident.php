<?php

namespace App\Actions\Organization\Resident;

use App\Actions\Common\LogActivity;
use App\Data\ActivityLogData;
use App\Data\OccupancyData;
use App\Data\ResidentData;
use App\Enums\ActivityEventEnum;
use App\Enums\OccupancyStatus;
use App\Enums\UnitStatus;
use App\Models\Organization;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class UpdateResident
{
    public static function handle(ResidentData $residentData, User $residentToEdit, User $actor, Organization $organization): User
    {
        $updatedResident = DB::transaction(function () use ($residentData, $residentToEdit, $organization): User {
            $lockedResident = User::query()->resident()
                ->whereHas('organizations', fn ($organizationMembershipQuery) => $organizationMembershipQuery
                    ->where('organizations.id', $organization->id)
                    ->where('organization_user.is_active', true))
                ->whereKey($residentToEdit->id)
                ->lockForUpdate()
                ->firstOrFail();

            $activeOccupanciesBeforeLock = $lockedResident->occupancies()->active()->get();
            $hasMultipleActiveOccupancies = $activeOccupanciesBeforeLock->count() > 1;

            if ($hasMultipleActiveOccupancies) {
                throw ValidationException::withMessages(['cannot_submit' => __('This resident has multiple active occupancies. Resolve them before editing.')]);
            }

            $occupancyBeforeLock = $activeOccupanciesBeforeLock->first();
            $unitIdsToLock = array_unique(array_filter([$occupancyBeforeLock?->unit_id, $residentData->unit_id]));
            sort($unitIdsToLock);

            $lockedUnitsById = Unit::query()->whereIn('id', $unitIdsToLock)->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            $lockedActiveOccupancies = $lockedResident->occupancies()->active()->lockForUpdate()->get();
            $occupancyChangedWhileLocking = $lockedActiveOccupancies->count() > 1
                || $lockedActiveOccupancies->first()?->id !== $occupancyBeforeLock?->id;

            if ($occupancyChangedWhileLocking) {
                throw ValidationException::withMessages(['cannot_submit' => __('The resident occupancy changed. Please try again.')]);
            }

            $currentOccupancy = $lockedActiveOccupancies->first();
            $destinationUnit = $lockedUnitsById->get($residentData->unit_id);
            $destinationUnitIsInvalid = $destinationUnit === null
                || $destinationUnit->organization_id !== $organization->id
                || $destinationUnit->property_id !== $residentData->property_id;

            if ($destinationUnitIsInvalid) {
                throw ValidationException::withMessages(['unit_id' => __('Selected unit does not belong to the chosen property.')]);
            }

            $destinationUnitHasAnotherResident = $destinationUnit->occupancies()
                ->active()
                ->where('resident_id', '!=', $lockedResident->id)
                ->lockForUpdate()
                ->exists();

            if ($destinationUnitHasAnotherResident) {
                throw ValidationException::withMessages(['unit_id' => __('Selected unit is already occupied.')]);
            }

            $residenceIsChanging = $currentOccupancy?->unit_id !== $destinationUnit->id;

            if ($residenceIsChanging) {
                $destinationUnitHasAnyActiveOccupancy = $destinationUnit->occupancies()->active()->lockForUpdate()->exists();

                if ($destinationUnitHasAnyActiveOccupancy) {
                    throw ValidationException::withMessages(['unit_id' => __('Selected unit is already occupied.')]);
                }

                $hasCurrentOccupancy = $currentOccupancy !== null;

                if ($hasCurrentOccupancy) {
                    $currentOccupancy->update([
                        'status' => OccupancyStatus::ENDED,
                        'ends_at' => now()->toDateString(),
                    ]);

                    $previousUnit = $lockedUnitsById->get($currentOccupancy->unit_id);
                    $previousUnitCanBecomeVacant = $previousUnit !== null && ! $previousUnit->isUnderMaintenance();

                    if ($previousUnitCanBecomeVacant) {
                        $previousUnit->update(['status' => UnitStatus::VACANT]);
                    }
                }

                CreateOccupancy::handle(new OccupancyData(
                    organization_id: $organization->id,
                    unit_id: $destinationUnit->id,
                    resident_id: $lockedResident->id,
                    starts_at: now()->toDateString(),
                    status: OccupancyStatus::ACTIVE,
                ));

                $destinationUnitCanBecomeOccupied = ! $destinationUnit->isUnderMaintenance();

                if ($destinationUnitCanBecomeOccupied) {
                    $destinationUnit->update(['status' => UnitStatus::OCCUPIED]);
                }
            }

            $lockedResident->update([
                'name' => $residentData->name,
                'email' => $residentData->email,
                'phone' => $residentData->phone,
            ]);

            return $lockedResident;
        });

        LogActivity::handle(ActivityLogData::from([
            'event' => ActivityEventEnum::RESIDENT_UPDATED,
            'title' => 'Resident updated',
            'description' => Str::swap([
                ':actor' => $actor->name,
                ':resident' => $updatedResident->name,
            ], ':actor updated resident ":resident".'),
            'subject' => $updatedResident,
            'actor' => $actor,
            'organization' => $organization,
        ]));

        return $updatedResident;
    }
}
