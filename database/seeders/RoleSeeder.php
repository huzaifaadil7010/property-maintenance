<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissionRegistrar = app(PermissionRegistrar::class);
        $originalOrganizationId = $permissionRegistrar->getPermissionsTeamId();

        try {
            $permissionRegistrar->setPermissionsTeamId(null);

            DB::transaction(function (): void {
                foreach (UserRole::cases() as $role) {
                    Role::findOrCreate($role, 'web');
                }
            });
        } finally {
            $permissionRegistrar->setPermissionsTeamId($originalOrganizationId);
            $permissionRegistrar->forgetCachedPermissions();
        }
    }
}
