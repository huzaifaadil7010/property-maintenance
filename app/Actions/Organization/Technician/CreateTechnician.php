<?php

namespace App\Actions\Organization\Technician;

use App\Data\TechnicianData;
use App\Data\TechnicianProfileData;
use App\Data\TechnicianUserData;
use App\Enums\UserRole;
use App\Models\Organization;
use App\Models\TechnicianProfile;
use App\Models\User;
use App\Notifications\TechnicianAccountCreatedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class CreateTechnician
{
    public static function handle(TechnicianData $data, Organization $organization): User
    {
        $permissionRegistrar = app(PermissionRegistrar::class);
        $originalTeamId = $permissionRegistrar->getPermissionsTeamId();
        $temporaryPassword = Str::password(16, true, true, false, false);

        try {
            $permissionRegistrar->setPermissionsTeamId($organization->id);

            return DB::transaction(function () use ($data, $organization, $temporaryPassword): User {
                $technician = self::createTechnicianUser($data, $temporaryPassword);

                self::configureTechnician($technician, $organization);
                self::createTechnicianProfile($technician, $data, $organization);
                self::registerAccountNotification($technician, $temporaryPassword);

                return $technician;
            });
        } finally {
            $permissionRegistrar->setPermissionsTeamId($originalTeamId);
            $permissionRegistrar->forgetCachedPermissions();
        }
    }

    private static function createTechnicianUser(TechnicianData $data, string $temporaryPassword): User
    {
        return CreateUser::handle(new TechnicianUserData(
            name: $data->name,
            email: $data->email,
            password: $temporaryPassword,
            phone: $data->phone,
        ));
    }

    private static function configureTechnician(User $technician, Organization $organization): void
    {
        $technician->update(['current_organization_id' => $organization->id]);

        $technician->forceFill([
            'email_verified_at' => now(),
        ])->save();

        $technician->organizations()->syncWithoutDetaching([
            $organization->id => ['is_active' => true],
        ]);

        $technicianRole = Role::findByName(UserRole::TECHNICIAN->value, 'web');

        $technician->assignRole($technicianRole);
    }

    private static function createTechnicianProfile(
        User $technician,
        TechnicianData $data,
        Organization $organization,
    ): TechnicianProfile {
        return CreateTechnicianProfile::handle(new TechnicianProfileData(
            organization_id: $organization->id,
            user_id: $technician->id,
            specialty: $data->specialty,
            is_available: $data->is_available,
            phone: $data->phone,
        ));
    }

    private static function registerAccountNotification(User $technician, string $temporaryPassword): void
    {
        DB::afterCommit(function () use ($technician, $temporaryPassword): void {
            $technician->notify(new TechnicianAccountCreatedNotification($temporaryPassword));
        });
    }
}
