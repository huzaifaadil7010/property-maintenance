<?php

namespace App\Actions\Organization\Resident;

use App\Data\ResidentData;
use App\Data\OccupancyData;
use App\Data\ResidentUserData;
use App\Enums\OccupancyStatus;
use App\Enums\UserRole;
use App\Models\Organization;
use App\Models\Unit;
use App\Models\User;
use App\Notifications\ResidentAccountCreatedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class CreateResident
{
    public static function handle(ResidentData $data, Organization $organization): User
    {
        $permissionRegistrar = app(PermissionRegistrar::class);
        $originalTeamId = $permissionRegistrar->getPermissionsTeamId();
        $temporaryPassword = Str::password(16, true, true, false, false);

        try {
            $permissionRegistrar->setPermissionsTeamId($organization->id);

            return DB::transaction(function () use ($data, $organization, $temporaryPassword): User {
                $unit = Unit::query()
                    ->whereKey($data->unit_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($unit->property_id !== $data->property_id) {
                    throw ValidationException::withMessages([
                        'unit_id' => __('Selected unit does not belong to the chosen property.'),
                    ]);
                }

                $isOccupied = $unit->occupancies()
                    ->where('status', OccupancyStatus::ACTIVE)
                    ->lockForUpdate()
                    ->exists();

                if ($isOccupied) {
                    throw ValidationException::withMessages([
                        'unit_id' => __('Selected unit is already occupied.'),
                    ]);
                }

                $resident = CreateUser::handle(new ResidentUserData(
                    name: $data->name,
                    email: $data->email,
                    password: $temporaryPassword,
                    phone: $data->phone,
                ));

                $resident->update(['current_organization_id' => $organization->id]);

                $resident->forceFill([
                    'email_verified_at' => now(),
                ])->save();

                $resident->organizations()->syncWithoutDetaching([
                    $organization->id => ['is_active' => true],
                ]);

                $residentRole = Role::findByName(UserRole::RESIDENT->value, 'web');

                $resident->assignRole($residentRole);

                CreateOccupancy::handle(new OccupancyData(
                    organization_id: $organization->id,
                    unit_id: $unit->id,
                    resident_id: $resident->id,
                    starts_at: now()->toDateString(),
                    status: OccupancyStatus::ACTIVE,
                ));

                DB::afterCommit(function () use ($resident, $temporaryPassword): void {
                    $resident->notify(new ResidentAccountCreatedNotification($temporaryPassword));
                });

                return $resident;
            });
        } finally {
            $permissionRegistrar->setPermissionsTeamId($originalTeamId);
            $permissionRegistrar->forgetCachedPermissions();
        }
    }
}
