<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Stripe\Exception\ApiErrorException;

class DeletePaymentMethodController extends Controller
{
    public function __invoke(Request $request, PaymentMethod $paymentMethod): RedirectResponse
    {
        $organization = $request->user()->currentOrganization;
        abort_unless($paymentMethod->organization_id === $organization->id, 404);

        $subscription = $organization->subscription('default');

        if (($paymentMethod->is_default && $subscription?->valid()) || $subscription?->payment_method_id === $paymentMethod->id) {
            return back()->withErrors(['payment_method' => 'Choose another default card before deleting this payment method.']);
        }

        try {
            $organization->deletePaymentMethod($paymentMethod->stripe_payment_method_id);
            $paymentMethod->delete();

            return Inertia::flash('success', 'Payment method deleted.')->back();
        } catch (ApiErrorException $exception) {
            report($exception);

            return back()->withErrors(['payment_method' => 'Stripe could not delete this payment method.']);
        }
    }
}
