<?php

return [
    'defaults' => [
        'guard' => env('AUTH_GUARD', 'web'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'owners'),
    ],

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'owners',
        ],
    ],

    'providers' => [
        'owners' => [
            'driver' => 'eloquent',
            'model' => App\Models\Owner::class,
        ],
    ],

    'passwords' => [
        'owners' => [
            'provider' => 'owners',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => 10800,
];
