<?php

$frontendUrl = trim((string) env('https://promotional-material-khf5-111l6w5xn-axediant02s-projects.vercel.app', 'http://localhost:5173'), " \t\n\r\0\x0B\"'");

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => [],
    'allowed_origins_patterns' => [
        '#^https:\/\/.*\.vercel\.app$#',
    ],
    'allowed_headers' => ['*'],
    'supports_credentials' => true,
];