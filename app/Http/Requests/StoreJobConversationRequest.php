<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreJobConversationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'in:interview_prep,negotiation,followup'],
            'user_input' => ['nullable', 'string'],
            'currency' => ['nullable', 'string', 'size:3'],
            'offer_amount' => ['nullable', 'numeric', 'min:0'],
            'target_amount' => ['nullable', 'numeric', 'min:0'],
            'context' => ['nullable', 'string'],
        ];
    }
}
