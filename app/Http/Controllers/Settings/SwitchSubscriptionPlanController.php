<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\SwitchSubscriptionPlanRequest;
use App\Models\Plan;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Laravel\Cashier\Exceptions\IncompletePayment;
use Stripe\Exception\ApiErrorException;

class SwitchSubscriptionPlanController extends Controller
{
    public function __invoke(SwitchSubscriptionPlanRequest $request): RedirectResponse
    {
        $organization = $request->user()->currentOrganization;
        $subscription = $organization->subscription('default');
        $plan = Plan::query()->active()->findOrFail($request->integer('plan_id'));

        try {
            $subscription->swap([
                $plan->stripe_price_id => ['quantity' => 1],
            ]);
            $subscription->update(['plan_id' => $plan->id]);

            return Inertia::flash('success', 'Subscription plan updated.')->back();
        } catch (IncompletePayment|ApiErrorException $exception) {
            report($exception);

            return back()->withErrors(['plan_id' => 'Stripe could not switch the subscription plan.']);
        }
    }
}
