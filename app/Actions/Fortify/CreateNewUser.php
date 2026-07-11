<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Enums\UserRole;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            ...$this->profileRules(),
            'organization_name' => ['required', 'string', 'max:255'],
            'password' => $this->passwordRules(),
        ])->validate();

        return DB::transaction(function () use ($input): User {
            $user = User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => $input['password'],
            ]);

            $organizationUuid = (string) Str::uuid();

            $organization = Organization::create([
                'uuid' => $organizationUuid,
                'name' => $input['organization_name'],
                'slug' => Str::slug($input['organization_name']).'-'.Str::before($organizationUuid, '-'),
            ]);

            $user->update([
                'current_organization_id' => $organization->id,
            ]);

            $user->organizations()->syncWithoutDetaching([
                $organization->id => ['is_active' => true],
            ]);

            $permissionRegistrar = app(PermissionRegistrar::class);
            $originalOrganizationId = $permissionRegistrar->getPermissionsTeamId();

            try {
                $permissionRegistrar->setPermissionsTeamId($organization->id);

                $ownerRole = Role::findOrCreate(UserRole::OWNER, 'web');

                $user->assignRole($ownerRole);
            } finally {
                $permissionRegistrar->setPermissionsTeamId($originalOrganizationId);
            }

            return $user;
        });
    }
}
