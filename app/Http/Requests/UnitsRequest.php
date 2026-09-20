<?php

namespace App\Http\Requests;

use App\Actions\Billing\GetUnitCreationAllowance;
use App\Enums\UnitStatus;
use App\Models\Property;
use App\Models\Unit;
use Illuminate\Container\Attributes\RouteParameter as RouteParam;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
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
        return [
            'property_id' => [
                'required',
                'integer',
                Rule::exists(Property::class, 'id')->where(
                    'organization_id',
                    $this->user()?->current_organization_id,
                ),
            ],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique(Unit::class, 'name')
                    ->ignore($unit)
                    ->where(
                        'property_id',
                        $this->integer('property_id'),
                    ),
            ],
            'floor' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::enum(UnitStatus::class)],
        ];
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
