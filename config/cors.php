<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie', '/'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'http://localhost:5173',
        'http://localhost:8080', // Add this line
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,
    
    'credentials' => true,
];
