<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => [
        'https://promotional-material-khf5.vercel.app',
        'http://localhost:5173',
        'http://localhost:8000',
    ],
    'allowed_origins_patterns' => [
        '#^https:\/\/[a-zA-Z0-9-]+\.vercel\.app$#',
    ],
    'allowed_headers' => ['*'],
    'supports_credentials' => true,
];
