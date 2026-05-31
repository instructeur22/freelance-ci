<?php
return [
    "api_key" => env("GENIUS_PAY_API_KEY", ""),
    "site_id" => env("GENIUS_PAY_SITE_ID", ""),
    "webhook_secret" => env("GENIUS_PAY_WEBHOOK_SECRET", ""),
    "mode" => env("GENIUS_PAY_MODE", "test"),
    "timeout" => env("GENIUS_PAY_TIMEOUT_SECONDS", 120),
];
