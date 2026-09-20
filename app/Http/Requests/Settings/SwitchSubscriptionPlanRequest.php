<?php

namespace App\Http\Requests\Settings;

use App\Models\Plan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class SwitchSubscriptionPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['plan_id' => ['required', 'integer', Rule::exists(Plan::class, 'id')->where('is_active', true)]];
    }

    public function after(): array
    {
        $organization = $this->user()?->currentOrganization;

        return [function (Validator $validator) use ($organization): void {
            $subscription = $organization?->subscription('default');
            $plan = Plan::query()->active()->find($this->integer('plan_id'));

            if ($subscription === null || ! $subscription->valid() || $subscription->canceled()) {
                $validator->errors()->add('subscription', 'Only an active subscription can switch plans.');

                return;
            }

            if ($subscription->plan_id === $plan?->id) {
                $validator->errors()->add('plan_id', 'This is already the current plan.');
            }

        }];
    }
}
