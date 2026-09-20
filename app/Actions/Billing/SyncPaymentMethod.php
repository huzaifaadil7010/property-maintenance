<?php

namespace App\Actions\Billing;

use App\Models\Organization;
use App\Models\PaymentMethod;
use Illuminate\Support\Facades\DB;
use Laravel\Cashier\PaymentMethod as CashierPaymentMethod;

class SyncPaymentMethod
{
    public static function handle(
        Organization $organization,
        CashierPaymentMethod $cashierPaymentMethod,
        bool $makeDefault = false,
    ): PaymentMethod {
        $stripePaymentMethod = $cashierPaymentMethod->asStripePaymentMethod();

        return DB::transaction(function () use ($organization, $stripePaymentMethod, $makeDefault): PaymentMethod {
            if ($makeDefault) {
                $organization->paymentMethods()->update(['is_default' => false]);
            }

            return $organization->paymentMethods()->updateOrCreate(
                ['stripe_payment_method_id' => $stripePaymentMethod->id],
                [
                    'type' => $stripePaymentMethod->type,
                    'brand' => $stripePaymentMethod->card?->brand,
                    'last_four' => $stripePaymentMethod->card?->last4,
                    'exp_month' => $stripePaymentMethod->card?->exp_month,
                    'exp_year' => $stripePaymentMethod->card?->exp_year,
                    'is_default' => $makeDefault,
                ],
            );
        });
    }
}
