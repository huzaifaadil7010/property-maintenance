<?php

namespace App\Http\Requests;

use App\Actions\Billing\GetUnitCreationAllowance;
use App\Models\Unit;
use App\Validation\OrganizationInputRules;
use Illuminate\Container\Attributes\RouteParameter as RouteParam;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use Throwable;

class UnitsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(
        #[RouteParam('unit')] ?Unit $unit = null,
    ): array {
        return OrganizationInputRules::unit(
            $this->user()?->current_organization_id ?? 0,
            $this->integer('property_id'),
            $unit,
        );
    }

    public function after(
        #[RouteParam('unit')] ?Unit $unit = null,
    ): array {
        return [function (Validator $validator) use ($unit): void {
            if ($unit !== null) {
                return;
            }

            $organization = $this->user()?->currentOrganization;

            if ($organization === null) {
                return;
            }

            try {
                $allowance = GetUnitCreationAllowance::handle($organization);

                if ($allowance['remaining'] === 0) {
                    $validator->errors()->add(
                        'cannot_submit',
                        "This organization has used all {$allowance['limit']} unit creations for the current billing period.",
                    );
                }
            } catch (Throwable $exception) {
                report($exception);
                $validator->errors()->add('cannot_submit', 'The unit creation allowance could not be verified. Please try again.');
            }
        }];
    }
}
