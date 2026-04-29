<?php

$frontendUrl = trim((string) env('FRONTEND_URL', 'http://localhost:5173'), " \t\n\r\0\x0B\"'");

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => [$frontendUrl],
    'allowed_origins_patterns' => [
        '#^https:\/\/.*\.vercel\.app$#',
        '#^https:\/\/.*-axediant02s-projects\.vercel\.app$#',
    ],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];
