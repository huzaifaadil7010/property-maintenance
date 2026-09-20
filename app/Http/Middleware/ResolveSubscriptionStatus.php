<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use App\Models\Subscription;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveSubscriptionStatus
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $organization = $user?->currentOrganization;
        $subscription = $organization?->subscription('default');

        if ($user !== null && $organization !== null) {
            setPermissionsTeamId($organization->id);
            $user->unsetRelation('roles')->unsetRelation('permissions');
        }

        $request->attributes->set('currentSubscription', $subscription);
        $request->attributes->set('subscriptionAccess', [
            'state' => $this->state($subscription),
            'hasPanelAccess' => $subscription?->valid() ?? false,
            'canManageBilling' => $user?->hasRole(UserRole::OWNER) ?? false,
            'message' => $this->message($subscription),
        ]);

        return $next($request);
    }

    private function state(?Subscription $subscription): string
    {
        if ($subscription === null) {
            return 'none';
        }

        if ($subscription->onGracePeriod()) {
            return 'grace-period';
        }

        if ($subscription->onTrial()) {
            return 'trialing';
        }

        if ($subscription->ended()) {
            return 'ended';
        }

        return match ($subscription->stripe_status) {
            'active' => 'active',
            'incomplete', 'incomplete_expired' => 'incomplete',
            'past_due', 'unpaid' => 'past-due',
            default => $subscription->stripe_status,
        };
    }

    private function message(?Subscription $subscription): ?string
    {
        if ($subscription?->valid()) {
            return $subscription->onGracePeriod()
                ? 'Recurring billing is canceled. Access remains available until the current period ends.'
                : null;
        }

        return match ($this->state($subscription)) {
            'none' => 'Your organization needs an active subscription to access the panel.',
            'incomplete' => 'Your organization’s subscription requires payment confirmation.',
            'past-due' => 'Your organization’s subscription payment is past due.',
            default => 'Your organization’s subscription period has ended and the plan has not been renewed.',
        };
    }
}
