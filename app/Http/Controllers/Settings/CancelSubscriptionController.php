<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Stripe\Exception\ApiErrorException;

class CancelSubscriptionController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $subscription = $request->user()->currentOrganization->subscription('default');

        if ($subscription === null || ! $subscription->valid() || $subscription->canceled()) {
            return back()->withErrors(['subscription' => 'Only a current recurring subscription can be canceled.']);
        }

        try {
            $subscription->cancel();

            return Inertia::flash('success', 'Recurring billing has been canceled at the end of the current period.')->back();
        } catch (ApiErrorException $exception) {
            report($exception);

            return back()->withErrors(['subscription' => 'Stripe could not cancel the subscription.']);
        }
    }
}
