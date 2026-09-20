<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsSubscribed
{
    public function handle(Request $request, Closure $next): Response
    {
        $access = $request->attributes->get('subscriptionAccess', []);

        if (! ($access['hasPanelAccess'] ?? false)) {
            return redirect()->route('billing.index')->with('error', $access['message'] ?? 'An active subscription is required.');
        }

        return $next($request);
    }
}
