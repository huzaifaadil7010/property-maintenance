<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use LogicException;
use Stripe\Exception\ApiErrorException;

class ResumeSubscriptionController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $subscription = $request->user()->currentOrganization->subscription('default');

        if ($subscription === null || ! $subscription->onGracePeriod()) {
            return back()->withErrors(['subscription' => 'Only a subscription in its grace period can be resumed.']);
        }

        try {
            $subscription->resume();

            return Inertia::flash('success', 'Subscription resumed successfully.')->back();
        } catch (LogicException|ApiErrorException $exception) {
            report($exception);

            return back()->withErrors(['subscription' => 'Stripe could not resume the subscription.']);
        }
    }
}
