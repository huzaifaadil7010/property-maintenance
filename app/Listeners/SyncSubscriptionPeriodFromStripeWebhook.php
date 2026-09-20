<?php

namespace App\Listeners;

use App\Actions\Billing\SyncSubscriptionPeriod;
use App\Models\Subscription;
use Laravel\Cashier\Events\WebhookHandled;
use Throwable;

class SyncSubscriptionPeriodFromStripeWebhook
{
    public function handle(WebhookHandled $event): void
    {
        try {
            $type = $event->payload['type'] ?? null;

            if (in_array($type, ['customer.subscription.created', 'customer.subscription.updated'], true)) {
                $stripeSubscription = $event->payload['data']['object'] ?? [];
                $subscription = Subscription::query()->where('stripe_id', $stripeSubscription['id'] ?? null)->first();

                if ($subscription !== null) {
                    SyncSubscriptionPeriod::handle($subscription, $stripeSubscription);
                }

                return;
            }

            if ($type !== 'invoice.payment_succeeded') {
                return;
            }

            $invoice = $event->payload['data']['object'] ?? [];
            $stripeSubscriptionId = $invoice['parent']['subscription_details']['subscription']
                ?? $invoice['subscription']
                ?? null;
            $subscription = Subscription::query()->where('stripe_id', $stripeSubscriptionId)->first();

            if ($subscription !== null) {
                SyncSubscriptionPeriod::handle($subscription);
            }
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
