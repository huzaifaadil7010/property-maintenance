<?php

namespace App\Actions\Billing;

use App\Models\Subscription;
use Illuminate\Support\Carbon;

class SyncSubscriptionPeriod
{
    public static function handle(Subscription $subscription, ?array $stripeSubscription = null): Subscription
    {
        $stripeSubscription ??= $subscription->asStripeSubscription()->toArray();
        $firstItem = $stripeSubscription['items']['data'][0] ?? [];
        $periodStart = $firstItem['current_period_start'] ?? $stripeSubscription['current_period_start'] ?? null;
        $periodEnd = $firstItem['current_period_end'] ?? $stripeSubscription['current_period_end'] ?? null;

        if ($periodStart === null || $periodEnd === null) {
            return $subscription;
        }

        $subscription->update([
            'current_period_starts_at' => Carbon::createFromTimestamp($periodStart),
            'current_period_ends_at' => Carbon::createFromTimestamp($periodEnd),
        ]);

        return $subscription->refresh();
    }
}
