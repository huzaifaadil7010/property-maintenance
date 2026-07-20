<?php

namespace App\Http\Requests;

use App\Enums\UnitStatus;
use App\Models\Property;
use App\Models\Unit;
use Illuminate\Container\Attributes\RouteParameter as RouteParam;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
}
