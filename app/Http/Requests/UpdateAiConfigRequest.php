<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAiConfigRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()?->is_admin;
    }

    public function rules(): array
    {
        return [
            'provider' => ['required', 'string', 'in:gemini'],
            'model' => ['required', 'string', 'max:255'],
            'override_api_key' => ['nullable', 'string', 'max:255'],
            'reset_override' => ['nullable', 'boolean'],
        ];
    }
}
