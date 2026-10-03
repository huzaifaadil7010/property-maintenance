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
    public function authorize(#[RouteParameter('resident')] User $residentToUpdate): bool
    {
        $hasResidentRole = $residentToUpdate->hasRole(UserRole::RESIDENT);

        if (! $hasResidentRole) {
            return false;
        }

        $hasActiveOrganizationMembership = $residentToUpdate->organizations()
            ->whereKey($this->user()?->current_organization_id)
            ->wherePivot('is_active', true)
            ->exists();

        return $hasActiveOrganizationMembership;
    }

    public function rules(#[RouteParameter('resident')] User $residentToUpdate): array
    {
        return OrganizationInputRules::resident(
            $this->user()?->current_organization_id ?? 0,
            $this->integer('property_id'),
            $residentToUpdate,
        );
    }

    public function after(#[RouteParameter('resident')] User $residentToUpdate): array
    {
        return [
            function (Validator $inputValidator) use ($residentToUpdate): void {
                $hasInvalidResidenceInput = $inputValidator->errors()->hasAny(['property_id', 'unit_id']);

                if ($hasInvalidResidenceInput) {
                    return;
                }

                $selectedUnit = Unit::query()
                    ->whereKey($this->integer('unit_id'))
                    ->where('property_id', $this->integer('property_id'))
                    ->first();

                $selectedUnitIsUnavailable = $selectedUnit === null
                    || $selectedUnit->occupancies()->active()->where('resident_id', '!=', $residentToUpdate->id)->exists();

                if ($selectedUnitIsUnavailable) {
                    $inputValidator->errors()->add('unit_id', __('Selected unit is already occupied.'));
                }
            },
        ];
    }
}
