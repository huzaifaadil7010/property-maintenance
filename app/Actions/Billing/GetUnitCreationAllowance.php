<?php

namespace App\Actions\Billing;

use App\Models\Organization;
use App\Models\SubscriptionUnitUsage;
use RuntimeException;

class GetUnitCreationAllowance
{
    public static function handle(Organization $organization): array
    {
        $subscription = $organization->subscription('default');

        if ($subscription === null || ! $subscription->valid() || $subscription->plan === null) {
            throw new RuntimeException('A valid subscription plan is required.');
        }

        if (
            $subscription->current_period_starts_at === null
            || $subscription->current_period_ends_at === null
            || now()->greaterThanOrEqualTo($subscription->current_period_ends_at)
        ) {
            $subscription = SyncSubscriptionPeriod::handle($subscription);
        }

        if ($subscription->current_period_starts_at === null || $subscription->current_period_ends_at === null) {
            throw new RuntimeException('The current Stripe billing period could not be determined.');
        }

        $used = SubscriptionUnitUsage::query()
            ->where('subscription_id', $subscription->id)
            ->where('period_starts_at', $subscription->current_period_starts_at)
            ->value('units_created') ?? 0;
        $limit = $subscription->plan->unit_creation_limit;

        return [
            'subscription_id' => $subscription->id,
            'plan_id' => $subscription->plan->id,
            'period_starts_at' => $subscription->current_period_starts_at,
            'period_ends_at' => $subscription->current_period_ends_at,
            'used' => $used,
            'limit' => $limit,
            'remaining' => max($limit - $used, 0),
        ];
    }
}
