<?php

return [
    'currency' => env('BILLING_DEFAULT_CURRENCY', 'USD'),

    'killbill' => [
        'base_url' => env('KILLBILL_BASE_URL', 'http://killbill:8080'),
        'username' => env('KILLBILL_USERNAME', 'admin'),
        'password' => env('KILLBILL_PASSWORD', 'password'),
        'api_key' => env('KILLBILL_API_KEY', 'bob'),
        'api_secret' => env('KILLBILL_API_SECRET', 'lazar'),
    ],

    'token_alert_thresholds' => [
        'warning' => (float) env('BILLING_TOKEN_WARNING_THRESHOLD', 0.2),
        'critical' => (float) env('BILLING_TOKEN_CRITICAL_THRESHOLD', 0.05),
    ],
];
