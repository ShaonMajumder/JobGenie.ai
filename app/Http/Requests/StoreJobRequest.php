<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreJobRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'company_name' => ['required', 'string', 'max:255'],
            'company_website' => ['nullable', 'url', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'work_type' => ['required', 'in:remote,hybrid,onsite'],
            'job_type' => ['required', 'in:full_time,part_time,contract,internship'],
            'job_description' => ['required', 'string'],
            'job_description_url' => ['nullable', 'url', 'max:255'],
            'baseline_salary' => ['nullable', 'numeric', 'min:0'],
            'baseline_currency' => ['required', 'string', 'size:3'],
            'resume_text' => ['nullable', 'string'],
        ];
    }
}
