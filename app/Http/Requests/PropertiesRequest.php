<?php

namespace App\Http\Requests;

use App\Validation\OrganizationInputRules;
use Illuminate\Foundation\Http\FormRequest;

class PropertiesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return OrganizationInputRules::property();
    }
}
