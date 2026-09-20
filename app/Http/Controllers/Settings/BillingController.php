<?php

namespace App\Http\Controllers\Settings;

use App\Actions\Billing\GetActivePlans;
use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentMethodResource;
use App\Http\Resources\PlanResource;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BillingController extends Controller
{
    public function index(Request $request): Response
    {
        $organization = $request->user()->currentOrganization;
        $subscription = $organization->subscription('default')?->load('plan', 'paymentMethod');

        return Inertia::render('settings/billing', [
            'stripeKey' => config('cashier.key'),
            'plans' => PlanResource::collection(GetActivePlans::handle()),
            'unitCount' => $organization->units()->count(),
            'paymentMethods' => PaymentMethodResource::collection(
                $organization->paymentMethods()->orderByDesc('is_default')->latest()->get(),
            ),
            'subscription' => $subscription === null ? null : [
                'id' => $subscription->id,
                'plan_id' => $subscription->plan_id,
                'plan_name' => $subscription->plan?->name,
                'payment_method_id' => $subscription->payment_method_id,
                'status' => $subscription->stripe_status,
                'quantity' => $subscription->quantity,
                'trial_ends_at' => $subscription->trial_ends_at,
                'ends_at' => $subscription->ends_at,
                'is_valid' => $subscription->valid(),
                'is_canceled' => $subscription->canceled(),
                'on_grace_period' => $subscription->onGracePeriod(),
            ],
        ]);
    }
}
