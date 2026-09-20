<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOrganizationIsSubscribed
{
    public function handle(Request $request, Closure $next): Response
    {
        $access = $request->attributes->get('subscriptionAccess', []);

        if (! ($access['hasPanelAccess'] ?? false)) {
            $route = $request->user()?->hasRole(UserRole::OWNER) ? 'billing.index' : 'profile.edit';

            return redirect()->route($route)->with('error', $access['message'] ?? 'An active subscription is required.');
        }

        return $next($request);
    }
}
