<?php

namespace App\Http\Requests;

use App\Models\Unit;
use App\Validation\OrganizationInputRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ResidentsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return OrganizationInputRules::resident(
            $this->user()?->current_organization_id ?? 0,
            $this->integer('property_id'),
        );
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
