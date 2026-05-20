<?php

return [
    'paths'                    => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods'          => ['*'],
    'allowed_origins'          => array_filter([
        env('FRONTEND_URL'),            // Production frontend URL (Vercel / Cloudflare)
    ]),
    'allowed_origins_patterns' => [
        '#^http://localhost(:\d+)?$#',  // Local dev — any port
        '#^http://127\.0\.0\.1(:\d+)?$#',
    ],
    'allowed_headers'          => ['*'],
    'exposed_headers'          => [],
    'max_age'                  => 3600,
    'supports_credentials'     => false,
];
