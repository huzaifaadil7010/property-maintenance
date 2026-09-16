<?php

namespace Database\Seeders;

use App\Enums\OccupancyStatus;
use App\Enums\UserRole;
use App\Models\User;
use Database\Seeders\Support\DemoDataset;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;
use Spatie\Permission\PermissionRegistrar;

class OwnerPortfolioSeeder extends Seeder
{
    public function run(): void
    {
        $permissionRegistrar = app(PermissionRegistrar::class);
        $permissionRegistrar->forgetCachedPermissions();

        DB::transaction(function (): void {
            $organizationId = $this->seedOrganization();
            $roles = $this->roleIds();
            $password = Hash::make(DemoDataset::PASSWORD);
            $ownerId = $this->seedOwner($organizationId, $password);
            $propertyIds = $this->seedPropertiesAndUnits($organizationId);
            $this->seedMembershipAndRole($organizationId, $ownerId, $roles[UserRole::OWNER->value]);
            $this->seedResidents($organizationId, $propertyIds, $password, $roles[UserRole::RESIDENT->value]);
            $this->seedTechnicians($organizationId, $password, $roles[UserRole::TECHNICIAN->value]);
        });

        $permissionRegistrar->forgetCachedPermissions();
    }

    private function seedOrganization(): int
    {
        $organization = DemoDataset::organization();
        $timestamp = now()->subDays(65)->startOfDay()->addHours(9);

        DB::table('organizations')->updateOrInsert(
            ['slug' => $organization['slug']],
            [
                ...$organization,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
        );

        return DB::table('organizations')
            ->where('slug', DemoDataset::ORGANIZATION_SLUG)
            ->value('id');
    }

    private function seedOwner(int $organizationId, string $password): int
    {
        $owner = DemoDataset::owner();
        $timestamp = now()->subDays(64)->startOfDay()->addHours(10);

        DB::table('users')->updateOrInsert(
            ['email' => $owner['email']],
            [
                'current_organization_id' => $organizationId,
                'name' => $owner['name'],
                'email_verified_at' => $timestamp,
                'password' => $password,
                'phone' => $owner['phone'],
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
        );

        return DB::table('users')->where('email', $owner['email'])->value('id');
    }

    private function seedPropertiesAndUnits(int $organizationId): array
    {
        $propertyIds = [];

        foreach (DemoDataset::properties() as $propertyIndex => $property) {
            $propertyTimestamp = now()->subDays(62)->startOfDay()->addHours(9 + $propertyIndex);

            DB::table('properties')->updateOrInsert(
                ['organization_id' => $organizationId, 'name' => $property['name']],
                [
                    'type' => $property['type']->value,
                    'address' => $property['address'],
                    'city' => $property['city'],
                    'created_at' => $propertyTimestamp,
                    'updated_at' => $propertyTimestamp,
                ],
            );

            $propertyId = DB::table('properties')
                ->where('organization_id', $organizationId)
                ->where('name', $property['name'])
                ->value('id');
            $propertyIds[$property['name']] = $propertyId;

            foreach ($property['units'] as $unitIndex => $unit) {
                $unitTimestamp = now()->subDays(60)->startOfDay()->addMinutes(($propertyIndex * 30) + $unitIndex);

                DB::table('units')->updateOrInsert(
                    ['property_id' => $propertyId, 'name' => $unit['name']],
                    [
                        'organization_id' => $organizationId,
                        'floor' => $unit['floor'],
                        'status' => $unit['status']->value,
                        'created_at' => $unitTimestamp,
                        'updated_at' => $unitTimestamp,
                    ],
                );
            }
        }

        return $propertyIds;
    }

    private function seedResidents(int $organizationId, array $propertyIds, string $password, int $roleId): void
    {
        foreach (DemoDataset::residents() as $resident) {
            $timestamp = now()->subDays($resident['move_in_days_ago'])->startOfDay()->addHours(10);

            DB::table('users')->updateOrInsert(
                ['email' => $resident['email']],
                [
                    'current_organization_id' => $organizationId,
                    'name' => $resident['name'],
                    'email_verified_at' => $timestamp,
                    'password' => $password,
                    'phone' => $resident['phone'],
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ],
            );

            $residentId = DB::table('users')->where('email', $resident['email'])->value('id');
            $propertyId = $propertyIds[$resident['property']];
            $unitId = DB::table('units')
                ->where('property_id', $propertyId)
                ->where('name', $resident['unit'])
                ->value('id');

            $this->seedMembershipAndRole($organizationId, $residentId, $roleId);

            DB::table('occupancies')->updateOrInsert(
                ['unit_id' => $unitId, 'resident_id' => $residentId],
                [
                    'organization_id' => $organizationId,
                    'starts_at' => $timestamp->toDateString(),
                    'ends_at' => null,
                    'status' => OccupancyStatus::ACTIVE->value,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ],
            );
        }
    }

    private function seedTechnicians(int $organizationId, string $password, int $roleId): void
    {
        foreach (DemoDataset::technicians() as $technicianIndex => $technician) {
            $timestamp = now()->subDays(37 - $technicianIndex)->startOfDay()->addHours(9);

            DB::table('users')->updateOrInsert(
                ['email' => $technician['email']],
                [
                    'current_organization_id' => $organizationId,
                    'name' => $technician['name'],
                    'email_verified_at' => $timestamp,
                    'password' => $password,
                    'phone' => $technician['phone'],
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ],
            );

            $technicianId = DB::table('users')->where('email', $technician['email'])->value('id');
            $this->seedMembershipAndRole($organizationId, $technicianId, $roleId);

            DB::table('technician_profiles')->updateOrInsert(
                ['organization_id' => $organizationId, 'user_id' => $technicianId],
                [
                    'specialty' => $technician['specialty']->value,
                    'phone' => $technician['phone'],
                    'is_available' => $technician['is_available'],
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ],
            );
        }
    }

    private function seedMembershipAndRole(int $organizationId, int $userId, int $roleId): void
    {
        $timestamp = now();

        DB::table('organization_user')->updateOrInsert(
            ['organization_id' => $organizationId, 'user_id' => $userId],
            ['is_active' => true, 'created_at' => $timestamp, 'updated_at' => $timestamp],
        );

        DB::table(config('permission.table_names.model_has_roles'))->insertOrIgnore([
            'role_id' => $roleId,
            'model_type' => User::class,
            'model_id' => $userId,
            config('permission.column_names.team_foreign_key') => $organizationId,
        ]);
    }

    private function roleIds(): array
    {
        $roleIds = DB::table(config('permission.table_names.roles'))
            ->whereNull(config('permission.column_names.team_foreign_key'))
            ->where('guard_name', 'web')
            ->whereIn('name', [
                UserRole::OWNER->value,
                UserRole::RESIDENT->value,
                UserRole::TECHNICIAN->value,
            ])
            ->pluck('id', 'name');

        if ($roleIds->count() !== 3) {
            throw new RuntimeException('Run RoleSeeder before OwnerPortfolioSeeder.');
        }

        return $roleIds->all();
    }
}
