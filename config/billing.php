<?php

return [
    'currency' => env('BILLING_DEFAULT_CURRENCY', 'USD'),

    'token_alert_thresholds' => [
        'warning' => (float) env('BILLING_TOKEN_WARNING_THRESHOLD', 0.2),
        'critical' => (float) env('BILLING_TOKEN_CRITICAL_THRESHOLD', 0.05),
    ],
];
