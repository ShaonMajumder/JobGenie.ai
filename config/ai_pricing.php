<?php

return [
    'currency' => env('AI_BILLING_CURRENCY', env('BILLING_DEFAULT_CURRENCY', 'USD')),
    'providers' => [
        'gemini' => [
            'models' => [
                'gemini-2.5-flash' => [
                    'input_per_1k' => (float) env('AI_PRICING_GEMINI_FLASH_INPUT', 0.15),
                    'output_per_1k' => (float) env('AI_PRICING_GEMINI_FLASH_OUTPUT', 0.15),
                ],
                'gemini-1.5-pro' => [
                    'input_per_1k' => (float) env('AI_PRICING_GEMINI_PRO_INPUT', 3.50),
                    'output_per_1k' => (float) env('AI_PRICING_GEMINI_PRO_OUTPUT', 3.50),
                ],
            ],
        ],
    ],
];
