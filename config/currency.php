<?php

return [
    'api_key' => env('EXCHANGE_RATE_API_KEY'),
    'api_url' => env('EXCHANGE_RATE_API_URL', 'https://v6.exchangerate-api.com/v6'),
    'base_currency' => env('BASE_CURRENCY', 'USD'),
    'cache_duration' => 3600, // 1 hour in seconds
];
