<?php

namespace App\Mcp\Resident;

use App\Enums\UserRole;
use App\Models\User;
use Closure;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\PermissionRegistrar;

class ResidentContext
{
    public static function run(Closure $callback): mixed
    {
        abort_unless(app()->environment('local'), 403);

        $resident = User::query()->where('email', 'ethan.parker@northstar.test')->first();
        $organization = $resident?->currentOrganization;

        abort_unless(
            $organization !== null
            && $resident->organizations()->whereKey($organization->id)->wherePivot('is_active', true)->exists()
            && $organization->hasValidSubscription(),
            403,
        );

        $guard = Auth::guard();
        $previousUser = $guard->user();
        $registrar = app(PermissionRegistrar::class);
        $previousTeamId = $registrar->getPermissionsTeamId();

        try {
            $guard->setUser($resident);
            $registrar->setPermissionsTeamId($organization->id);
            $resident->unsetRelation('roles')->unsetRelation('permissions');

            abort_unless($resident->hasRole(UserRole::RESIDENT), 403);

            return $callback($resident, $organization);
        } finally {
            $registrar->setPermissionsTeamId($previousTeamId);
            $resident->unsetRelation('roles')->unsetRelation('permissions');

            if ($previousUser === null) {
                $guard->forgetUser();
            } else {
                $guard->setUser($previousUser);
            }
        }
    }
}
