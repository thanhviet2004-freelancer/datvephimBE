<?php

return [
    'paths' => ['api/*'],
    'allowed_methods' => ['GET', 'POST', 'OPTIONS'],
    'allowed_origins' => array_map('trim', explode(',', env('FRONTEND_URLS', 'http://127.0.0.1:8080,http://localhost:8080'))),
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['Content-Type', 'Accept'],
    'exposed_headers' => [],
    'max_age' => 600,
    'supports_credentials' => false,
];
