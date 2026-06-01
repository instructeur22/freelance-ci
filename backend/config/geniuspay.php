<?php

return [
    'api_key' => env('GENIUS_PAY_API_KEY'),
    'api_secret' => env('GENIUS_PAY_API_SECRET'),
    'webhook_secret' => env('GENIUS_PAY_WEBHOOK_SECRET'),
    'mode' => env('GENIUS_PAY_MODE', 'test'),
    'timeout' => env('GENIUS_PAY_TIMEOUT', 30),
];
