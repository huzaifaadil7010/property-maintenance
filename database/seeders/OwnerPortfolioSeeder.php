<?php

namespace Database\Seeders;

use App\Enums\UnitStatus;
use App\Enums\UserRole;
use App\Models\Organization;
use App\Models\Property;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class OwnerPortfolioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissionRegistrar = app(PermissionRegistrar::class);
        $originalOrganizationId = $permissionRegistrar->getPermissionsTeamId();

        $permissionRegistrar->forgetCachedPermissions();

        try {
            DB::transaction(function () use ($permissionRegistrar): void {
                foreach ($this->ownerPortfolios() as $portfolio) {
                    $user = User::query()->updateOrCreate(
                        ['email' => $portfolio['user']['email']],
                        $portfolio['user'],
                    );

                    $organization = Organization::query()->updateOrCreate(
                        ['uuid' => $portfolio['organization']['uuid']],
                        $portfolio['organization'],
                    );

                    $user->organizations()->syncWithoutDetaching([
                        $organization->id => ['is_active' => true],
                    ]);

                    $user->update(['current_organization_id' => $organization->id]);

                    $permissionRegistrar->setPermissionsTeamId($organization->id);

                    $ownerRole = Role::findByName(UserRole::OWNER, 'web');

                    $user->assignRole($ownerRole);

                    $property = Property::query()->updateOrCreate(
                        [
                            'organization_id' => $organization->id,
                            'name' => $portfolio['property']['name'],
                        ],
                        $portfolio['property'],
                    );

                    Unit::query()->updateOrCreate(
                        [
                            'property_id' => $property->id,
                            'name' => $portfolio['unit']['name'],
                        ],
                        [
                            ...$portfolio['unit'],
                            'organization_id' => $organization->id,
                        ],
                    );
                }
            });
        } finally {
            $permissionRegistrar->setPermissionsTeamId($originalOrganizationId);
            $permissionRegistrar->forgetCachedPermissions();
        }
    }

    /**
     * @return array<int, array{
     *     user: array{name: string, email: string, email_verified_at: \DateTimeInterface, password: string, phone: string},
     *     organization: array{uuid: string, name: string, slug: string, email: string, phone: string},
     *     property: array{name: string, type: string, address: string, city: string},
     *     unit: array{name: string, floor: string, status: UnitStatus}
     * }>
     */
    private function ownerPortfolios(): array
    {
        return [
            [
                'user' => [
                    'name' => 'Alex Morgan',
                    'email' => 'alex.owner@example.com',
                    'email_verified_at' => now(),
                    'password' => 'password',
                    'phone' => '+92 300 1111111',
                ],
                'organization' => [
                    'uuid' => '10000000-0000-4000-8000-000000000001',
                    'name' => 'Horizon Property Management',
                    'slug' => 'horizon-property-management',
                    'email' => 'contact@horizon.example.com',
                    'phone' => '+92 300 1111111',
                ],
                'property' => [
                    'name' => 'Green View Apartments',
                    'type' => 'apartment',
                    'address' => '12 Garden Road',
                    'city' => 'Lahore',
                ],
                'unit' => [
                    'name' => 'A-101',
                    'floor' => '1',
                    'status' => UnitStatus::VACANT,
                ],
            ],
            [
                'user' => [
                    'name' => 'Sara Ahmed',
                    'email' => 'sara.owner@example.com',
                    'email_verified_at' => now(),
                    'password' => 'password',
                    'phone' => '+92 300 2222222',
                ],
                'organization' => [
                    'uuid' => '20000000-0000-4000-8000-000000000002',
                    'name' => 'Summit Property Care',
                    'slug' => 'summit-property-care',
                    'email' => 'contact@summit.example.com',
                    'phone' => '+92 300 2222222',
                ],
                'property' => [
                    'name' => 'City Tower',
                    'type' => 'apartment',
                    'address' => '45 Main Boulevard',
                    'city' => 'Karachi',
                ],
                'unit' => [
                    'name' => 'B-201',
                    'floor' => '2',
                    'status' => UnitStatus::VACANT,
                ],
            ],
        ];
    }
}
