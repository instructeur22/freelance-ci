<?php

return [
    'api_key' => env('GENIUS_PAY_API_KEY', 'pk_test_default'),
    'api_secret' => env('GENIUS_PAY_API_SECRET', 'sk_test_default'),
    'webhook_secret' => env('GENIUS_PAY_WEBHOOK_SECRET', 'whsec_test_default'),
    'mode' => env('GENIUS_PAY_MODE', 'test'),
    'timeout' => env('GENIUS_PAY_TIMEOUT', 30),
];
