<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateLocalMcpOwner
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(app()->environment('local') && in_array($request->ip(), ['127.0.0.1', '::1'], true), 403);

        $ownerId = filter_var(config('organization_mcp.local_owner_id'), FILTER_VALIDATE_INT);

        abort_unless($ownerId !== false && $ownerId > 0, 403);

        $owner = User::query()->find($ownerId);
        $organization = $owner?->currentOrganization;

        abort_unless(
            $organization !== null
            && $owner->organizations()->whereKey($organization->id)->wherePivot('is_active', true)->exists()
            && $organization->hasValidSubscription(),
            403,
        );

        Auth::setUser($owner);
        setPermissionsTeamId($organization->id);
        $owner->unsetRelation('roles')->unsetRelation('permissions');

        abort_unless($owner->hasRole(UserRole::OWNER), 403);

        return $next($request);
    }
}
