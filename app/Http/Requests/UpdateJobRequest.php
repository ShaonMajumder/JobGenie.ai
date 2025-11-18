<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateJobRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'company_name' => ['sometimes', 'string', 'max:255'],
            'company_website' => ['sometimes', 'nullable', 'url', 'max:255'],
            'location' => ['sometimes', 'nullable', 'string', 'max:255'],
            'work_type' => ['sometimes', 'in:remote,hybrid,onsite'],
            'job_type' => ['sometimes', 'in:full_time,part_time,contract,internship'],
            'job_description' => ['sometimes', 'string'],
            'job_description_url' => ['sometimes', 'nullable', 'url', 'max:255'],
            'baseline_salary' => ['nullable', 'numeric', 'min:0'],
            'baseline_currency' => ['sometimes', 'string', 'size:3'],
            'status' => ['sometimes', 'in:not_applied,applied,interview_scheduled,offer_received,rejected,on_hold'],
        ];
    }
}
