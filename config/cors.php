<?php

return [

    // Allow CORS for API and static asset paths used by the frontend
    'paths' => ['api/*', 'sanctum/csrf-cookie', 'images/*', 'covers/*', 'audio/*'],

    'allowed_methods' => ['*'],

    // Frontend URLs (localhost for dev, production URLs for prod)
    'allowed_origins' => [
        'http://localhost:3000',
        'http://localhost:3001', 
        'http://localhost:3002',
        'http://127.0.0.1:3000',
        'http://127.0.0.1:3001',
        'http://127.0.0.1:3002',
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => ['ETag', 'x-amz-request-id', 'x-amz-id-2', 'x-amz-meta-*'],

    'max_age' => 0,

    // Required for Bearer token + Sanctum cookie auth
    'supports_credentials' => true,

];