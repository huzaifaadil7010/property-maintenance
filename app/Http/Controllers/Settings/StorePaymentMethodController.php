<?php

namespace App\Http\Controllers\Settings;

use App\Actions\Billing\SyncPaymentMethod;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\StorePaymentMethodRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Stripe\Exception\ApiErrorException;

class StorePaymentMethodController extends Controller
{
    public function __invoke(StorePaymentMethodRequest $request): RedirectResponse
    {
        $organization = $request->user()->currentOrganization;
        $paymentMethodId = $request->string('payment_method')->toString();

        try {
            $cashierPaymentMethod = $organization->addPaymentMethod($paymentMethodId);
            $makeDefault = ! $organization->paymentMethods()->exists();

            if ($makeDefault) {
                $organization->updateDefaultPaymentMethod($paymentMethodId);
            }

            SyncPaymentMethod::handle($organization, $cashierPaymentMethod, $makeDefault);

            return Inertia::flash('success', 'Payment method added successfully.')->back();
        } catch (ApiErrorException $exception) {
            report($exception);

            return back()->withErrors(['payment_method' => 'Stripe could not save this payment method.']);
        }
    }
}
