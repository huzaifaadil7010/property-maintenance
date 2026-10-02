<?php

namespace App\Http\Requests;

use App\Enums\UserRole;
use App\Models\Unit;
use App\Models\User;
use App\Validation\OrganizationInputRules;
use Illuminate\Container\Attributes\RouteParameter;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateResidentRequest extends FormRequest
{
    public function authorize(#[RouteParameter('resident')] User $resident): bool
    {
        return $resident->hasRole(UserRole::RESIDENT)
            && $resident->organizations()
                ->whereKey($this->user()?->current_organization_id)
                ->wherePivot('is_active', true)
                ->exists();
    }

    public function rules(#[RouteParameter('resident')] User $resident): array
    {
        return OrganizationInputRules::resident(
            $this->user()?->current_organization_id ?? 0,
            $this->integer('property_id'),
            $resident,
        );
    }

    public function after(#[RouteParameter('resident')] User $resident): array
    {
        return [
            function (Validator $validator) use ($resident): void {
                if ($validator->errors()->hasAny(['property_id', 'unit_id'])) {
                    return;
                }

                $unit = Unit::query()
                    ->whereKey($this->integer('unit_id'))
                    ->where('property_id', $this->integer('property_id'))
                    ->first();

                if ($unit === null || $unit->occupancies()->active()->where('resident_id', '!=', $resident->id)->exists()) {
                    $validator->errors()->add('unit_id', __('Selected unit is already occupied.'));
                }
            },
        ];
    }
}
