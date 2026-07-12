<?php

namespace App\Http\Middleware;

use App\Models\Organization;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class SetCurrentOrganization
{
    public function handle(Request $request, Closure $next): Response
    {
        $currentOrganization = $request->user()?->currentOrganization;

        abort_if($currentOrganization === null, 403);

        URL::defaults([
            'organization' => $currentOrganization->uuid,
        ]);

        $routeOrganization = $request->route('organization');

        if ($routeOrganization !== null) {
            $routeOrganizationUuid = $routeOrganization instanceof Organization
                ? $routeOrganization->uuid
                : (string) $routeOrganization;

            abort_unless(hash_equals($currentOrganization->uuid, $routeOrganizationUuid), 404);
        }

        return $next($request);
    }
}
