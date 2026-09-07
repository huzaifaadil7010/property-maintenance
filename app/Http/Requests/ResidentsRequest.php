<?php

namespace App\Http\Requests;

use App\Concerns\ProfileValidationRules;
use App\Models\Property;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ResidentsRequest extends FormRequest
{
    use ProfileValidationRules;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => $this->nameRules(),
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'phone' => ['nullable', 'string', 'max:255'],
            'property_id' => [
                'required',
                'integer',
                Rule::exists(Property::class, 'id')->where(
                    'organization_id',
                    $this->user()?->current_organization_id,
                ),
            ],
            'unit_id' => [
                'required',
                'integer',
                Rule::exists(Unit::class, 'id')->where(
                    'organization_id',
                    $this->user()?->current_organization_id,
                )->where(
                    'property_id',
                    $this->integer('property_id'),
                ),
            ],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $propertyId = $this->integer('property_id');

                $hasAvailableUnits = Unit::query()
                    ->where('property_id', $propertyId)
                    ->whereDoesntHave('occupancies', fn ($query) => $query->active())
                    ->exists();

                if (! $hasAvailableUnits) {
                    $validator->errors()->add(
                        'unit_id',
                        __('No available units are left for this property.'),
                    );
                }
            },
            function (Validator $validator): void {
                $propertyId = $this->integer('property_id');
                $unitId = $this->integer('unit_id');

                $isAvailableUnit = Unit::query()
                    ->whereKey($unitId)
                    ->where('property_id', $propertyId)
                    ->whereDoesntHave('occupancies', fn ($query) => $query->active())
                    ->exists();

                if (! $isAvailableUnit) {
                    $validator->errors()->add(
                        'unit_id',
                        __('Selected unit is already occupied.'),
                    );
                }
            },
        ];
    }
}
