<?php

namespace App\Http\Controllers\Settings;

use App\Actions\Billing\SyncPaymentMethod;
use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Stripe\Exception\ApiErrorException;

class SetDefaultPaymentMethodController extends Controller
{
    public function __invoke(Request $request, PaymentMethod $paymentMethod): RedirectResponse
    {
        $organization = $request->user()->currentOrganization;
        abort_unless($paymentMethod->organization_id === $organization->id, 404);

        try {
            $cashierPaymentMethod = $organization->updateDefaultPaymentMethod($paymentMethod->stripe_payment_method_id);
            $paymentMethod = SyncPaymentMethod::handle($organization, $cashierPaymentMethod, true);
            $organization->subscription('default')?->update(['payment_method_id' => $paymentMethod->id]);

            return Inertia::flash('success', 'Default payment method updated.')->back();
        } catch (ApiErrorException $exception) {
            report($exception);

            return back()->withErrors(['payment_method' => 'Stripe could not update the default payment method.']);
        }
    }
}
