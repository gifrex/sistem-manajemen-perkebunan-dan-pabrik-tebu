<?php

return [

    'defaults' => [
        'guard' => env('AUTH_GUARD', 'web'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'users'),
    ],

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],

        'api_client' => [
            'driver'   => 'sanctum',
            'provider' => 'api_clients',
        ],
    ],

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => env('AUTH_MODEL', App\Models\User::class),
        ],

        'api_clients' => [
            'driver' => 'eloquent',
            'model'  => App\Models\ApiClient::class,
        ],

        // 'users' => [
        //     'driver' => 'database',
        //     'table' => 'username',
        //     'model' => App\Models\Username::class, // Pastikan model User sesuai
        //     'username' => 'usernm', // Ganti dengan kolom username
        // ],
    ],

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),

];
