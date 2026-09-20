<?php

namespace App\Http\Requests\Settings;

use App\Models\PaymentMethod;
use App\Models\Plan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'plan_id' => ['required', 'integer', Rule::exists(Plan::class, 'id')->where('is_active', true)],
            'stored_payment_method_id' => [
                'nullable',
                'integer',
                Rule::exists(PaymentMethod::class, 'id')->where('organization_id', $this->user()?->current_organization_id),
                'required_without:new_stripe_payment_method_id',
            ],
            'new_stripe_payment_method_id' => ['nullable', 'string', 'max:255', 'required_without:stored_payment_method_id'],
        ];
    }

    public function after(): array
    {
        $organization = $this->user()?->currentOrganization;

        return [
            function (Validator $validator): void {
                if ($this->filled('stored_payment_method_id') && $this->filled('new_stripe_payment_method_id')) {
                    $validator->errors()->add('payment_method', 'Choose a stored card or add a new card, not both.');
                }
            },
            function (Validator $validator) use ($organization): void {
                if ($organization === null) {
                    return;
                }

                $subscription = $organization->subscription('default');

                if ($subscription !== null && ! $subscription->ended()) {
                    $validator->errors()->add('subscription', 'This organization already has a current subscription.');
                }
            },
            function (Validator $validator) use ($organization): void {
                $plan = Plan::query()->active()->find($this->integer('plan_id'));

                if ($organization !== null && $plan !== null && ! $plan->supportsUnitCount($organization->units()->count())) {
                    $validator->errors()->add('plan_id', 'This plan does not support the organization’s current unit count.');
                }
            },
        ];
    }
}
