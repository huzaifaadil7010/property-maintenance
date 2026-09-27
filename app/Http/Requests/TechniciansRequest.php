<?php

namespace App\Http\Requests;

use App\Validation\OrganizationInputRules;
use Illuminate\Foundation\Http\FormRequest;

class TechniciansRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return OrganizationInputRules::technician();
    }
}
