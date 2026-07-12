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
        self::validateUser($input, $this->profileRules(), $this->passwordRules());

        return DB::transaction(function () use ($input): User {
            $user = self::createUser($input);
            $organization = self::createOrganization($input['organization_name']);

            self::updateCurrentOrganization($user, $organization);
            self::attachOrganization($user, $organization);
            self::assignOwnerRole($user, $organization);

            return $user;
        });
    }

    private static function validateUser(array $input, array $profileRules, array $passwordRules): void
    {
        Validator::make($input, [
            ...$profileRules,
            'organization_name' => ['required', 'string', 'max:255'],
            'password' => $passwordRules,
        ])->validate();
    }

    private static function createUser(array $input): User
    {
        return User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => $input['password'],
        ]);
    }

    private static function createOrganization(string $name): Organization
    {
        $uuid = (string) Str::uuid();

        return Organization::create([
            'uuid' => $uuid,
            'name' => $name,
            'slug' => Str::slug($name),
        ]);
    }

    private static function updateCurrentOrganization(User $user, Organization $organization): void
    {
        $user->update([
            'current_organization_id' => $organization->id,
        ]);
    }

    private static function attachOrganization(User $user, Organization $organization): void
    {
        $user->organizations()->syncWithoutDetaching([
            $organization->id => ['is_active' => true],
        ]);
    }

    private static function assignOwnerRole(User $user, Organization $organization): void
    {
        app(PermissionRegistrar::class)->setPermissionsTeamId($organization->id);

        $ownerRole = Role::findByName(UserRole::OWNER, 'web');

        $user->assignRole($ownerRole);
    }
}
