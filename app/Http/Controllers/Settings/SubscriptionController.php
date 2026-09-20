<?php

namespace App\Http\Controllers\Settings;

use App\Actions\Billing\SyncPaymentMethod;
use App\Actions\Billing\SyncSubscriptionPeriod;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\StoreSubscriptionRequest;
use App\Models\PaymentMethod;
use App\Models\Plan;
use Inertia\Inertia;
use Laravel\Cashier\Exceptions\IncompletePayment;
use Stripe\Exception\ApiErrorException;
use Symfony\Component\HttpFoundation\Response;

class SubscriptionController extends Controller
{
    public function store(StoreSubscriptionRequest $request): Response
    {
        $organization = $request->user()->currentOrganization;
        $plan = Plan::query()->active()->findOrFail($request->integer('plan_id'));
        $localPaymentMethod = $request->filled('stored_payment_method_id')
            ? PaymentMethod::query()->findOrFail($request->integer('stored_payment_method_id'))
            : null;
        $stripePaymentMethodId = $localPaymentMethod?->stripe_payment_method_id
            ?? $request->string('new_stripe_payment_method_id')->toString();

        try {
            $builder = $organization
                ->newSubscription('default', $plan->stripe_price_id);

            if (! $organization->subscriptions()->exists()) {
                $builder->trialDays($plan->trial_days);
            }

            $subscription = $builder->create($stripePaymentMethodId);
            $cashierPaymentMethod = $organization->findPaymentMethod($stripePaymentMethodId);
            $organization->updateDefaultPaymentMethod($stripePaymentMethodId);
            $localPaymentMethod ??= SyncPaymentMethod::handle($organization, $cashierPaymentMethod, true);

            if (! $localPaymentMethod->is_default) {
                $localPaymentMethod = SyncPaymentMethod::handle($organization, $cashierPaymentMethod, true);
            }

            $subscription->update([
                'plan_id' => $plan->id,
                'payment_method_id' => $localPaymentMethod->id,
            ]);

            try {
                SyncSubscriptionPeriod::handle($subscription);
            } catch (ApiErrorException $exception) {
                report($exception);
            }

            return Inertia::flash('success', 'Your subscription has started successfully.')->back();
        } catch (IncompletePayment $exception) {
            return Inertia::location(route('cashier.payment', [
                'id' => $exception->payment->id,
                'redirect' => route('billing.index'),
            ]));
        } catch (ApiErrorException $exception) {
            report($exception);

            return back()->withErrors(['payment_method' => 'Stripe could not create the subscription.']);
        }
    }
}
