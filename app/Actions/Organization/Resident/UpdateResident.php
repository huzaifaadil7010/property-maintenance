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
    public static function handle(ResidentData $data, User $resident, User $actor, Organization $organization): User
    {
        $updatedResident = DB::transaction(function () use ($data, $resident, $organization): User {
            $resident = User::query()->resident()
                ->whereHas('organizations', fn ($query) => $query
                    ->where('organizations.id', $organization->id)
                    ->where('organization_user.is_active', true))
                ->whereKey($resident->id)
                ->lockForUpdate()
                ->firstOrFail();

            $activeOccupancies = $resident->occupancies()->active()->get();

            if ($activeOccupancies->count() > 1) {
                throw ValidationException::withMessages(['cannot_submit' => __('This resident has multiple active occupancies. Resolve them before editing.')]);
            }

            $currentOccupancy = $activeOccupancies->first();
            $unitIds = array_unique(array_filter([$currentOccupancy?->unit_id, $data->unit_id]));
            sort($unitIds);

            $units = Unit::query()->whereIn('id', $unitIds)->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            $activeOccupancies = $resident->occupancies()->active()->lockForUpdate()->get();

            if ($activeOccupancies->count() > 1 || $activeOccupancies->first()?->id !== $currentOccupancy?->id) {
                throw ValidationException::withMessages(['cannot_submit' => __('The resident occupancy changed. Please try again.')]);
            }

            $currentOccupancy = $activeOccupancies->first();
            $newUnit = $units->get($data->unit_id);

            if ($newUnit === null || $newUnit->organization_id !== $organization->id || $newUnit->property_id !== $data->property_id) {
                throw ValidationException::withMessages(['unit_id' => __('Selected unit does not belong to the chosen property.')]);
            }

            if ($newUnit->occupancies()->active()->where('resident_id', '!=', $resident->id)->lockForUpdate()->exists()) {
                throw ValidationException::withMessages(['unit_id' => __('Selected unit is already occupied.')]);
            }

            if ($currentOccupancy?->unit_id !== $newUnit->id) {
                if ($newUnit->occupancies()->active()->lockForUpdate()->exists()) {
                    throw ValidationException::withMessages(['unit_id' => __('Selected unit is already occupied.')]);
                }

                if ($currentOccupancy !== null) {
                    $currentOccupancy->update([
                        'status' => OccupancyStatus::ENDED,
                        'ends_at' => now()->toDateString(),
                    ]);

                    $oldUnit = $units->get($currentOccupancy->unit_id);

                    if ($oldUnit !== null && ! $oldUnit->isUnderMaintenance()) {
                        $oldUnit->update(['status' => UnitStatus::VACANT]);
                    }
                }

                CreateOccupancy::handle(new OccupancyData(
                    organization_id: $organization->id,
                    unit_id: $newUnit->id,
                    resident_id: $resident->id,
                    starts_at: now()->toDateString(),
                    status: OccupancyStatus::ACTIVE,
                ));

                if (! $newUnit->isUnderMaintenance()) {
                    $newUnit->update(['status' => UnitStatus::OCCUPIED]);
                }
            }

            $resident->update([
                'name' => $data->name,
                'email' => $data->email,
                'phone' => $data->phone,
            ]);

            return $resident;
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
