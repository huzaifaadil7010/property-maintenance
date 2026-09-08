<?php

namespace App\Http\Requests;

use App\Concerns\ProfileValidationRules;
use App\Enums\TechnicianSpecialty;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TechniciansRequest extends FormRequest
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
            'specialty' => ['required', Rule::enum(TechnicianSpecialty::class)],
            'is_available' => ['boolean'],
        ];
    }
}
