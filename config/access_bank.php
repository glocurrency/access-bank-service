<?php

return [
    'api' => [
        'url' => env('ACCESS_API_URL'),
        'auth_url' => env('ACCESS_API_AUTH_URL'),
        'app_id' => env('ACCESS_API_APP_ID', '0000-0000'),
        'client_id' => env('ACCESS_API_CLIENT_ID'),
        'client_secret' => env('ACCESS_API_CLIENT_SECRET'),
        'resource_id' => env('ACCESS_API_RESOURCE_ID'),
        'subscription_key' => env('ACCESS_API_SUBSCRIPTION_KEY'),
    ],
];
