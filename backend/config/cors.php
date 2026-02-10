<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => [
        'http://localhost:5173',
        'http://172.20.228.41',
        'http://172.20.228.41:8000',
    ],
    'allowed_headers' => ['*'],
    'supports_credentials' => true,
];

