<?php

return [
    'timezone' => env('OPERATIONS_TIMEZONE', 'Asia/Kathmandu'),
    'recommendation_freshness_minutes' => (int) env('OPERATIONS_RECOMMENDATION_FRESHNESS_MINUTES', 30),
];
