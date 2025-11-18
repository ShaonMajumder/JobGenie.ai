<?php

return [
    'provider' => env('LLM_PROVIDER', 'gemini'),

    // 'model' => env('LLM_MODEL_NAME', 'gemini-1.5-pro'),
    'model' => env('GEMINI_MODEL', 'gemini-2.5-flash'),

    'providers' => [
        'gemini' => [
            'class' => App\Services\Llm\GeminiLlmService::class,
            'api_key' => env('GEMINI_API_KEY'),
            'timeout' => (int) env('GEMINI_TIMEOUT', 60),
        ],
    ],
];
