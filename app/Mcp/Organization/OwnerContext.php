<?php

namespace App\Mcp\Organization;

use App\Enums\UserRole;
use App\Models\User;
use Closure;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\PermissionRegistrar;

class OwnerContext
{
    public static function run(Closure $callback): mixed
    {
        abort_unless(app()->environment('local'), 403);

        $owner = User::query()->where('email', 'owner@northstar.test')->first();
        $organization = $owner?->currentOrganization;

        abort_unless(
            $organization !== null
            && $owner->organizations()->whereKey($organization->id)->wherePivot('is_active', true)->exists()
            && $organization->hasValidSubscription(),
            403,
        );

        $guard = Auth::guard();
        $previousUser = $guard->user();
        $registrar = app(PermissionRegistrar::class);
        $previousTeamId = $registrar->getPermissionsTeamId();

        try {
            $guard->setUser($owner);
            $registrar->setPermissionsTeamId($organization->id);
            $owner->unsetRelation('roles')->unsetRelation('permissions');

            abort_unless($owner->hasRole(UserRole::OWNER), 403);

            return $callback($owner, $organization);
        } finally {
            $registrar->setPermissionsTeamId($previousTeamId);
            $owner->unsetRelation('roles')->unsetRelation('permissions');

            if ($previousUser === null) {
                $guard->forgetUser();
            } else {
                $guard->setUser($previousUser);
            }
        }
    }
}
