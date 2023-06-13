<?php

return [
    'defaults' => [
        'guard' => 'admin',
        'passwords' => 'admins',
    ],

    'guards' => [
        'api' => [
            'driver' => 'jwt',
            'provider' => 'users',
        ],

        'admin' =>[
            'driver' => 'jwt',
            'provider' => 'admins'
        ],

        'rep' =>[
            'driver' => 'jwt',
            'provider' => 'reps'
        ],
    ],

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => \App\Models\User::class
        ],

        'admins' =>[
            'driver' => 'eloquent',
            'model'  => \App\Models\Admin::class
        ],

        'reps' =>[
            'driver' => 'eloquent',
            'model' => \App\Models\Rep::class

        ]
    ],

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => 'password_resets',
            'expire' => 60,
        ],
        'admins' => [
            'provider' => 'admins',
            'table' => 'admin_password_resets',
            'expire' => 60,
        ],
        'reps' => [
            'provider' => 'reps',
            'table' => 'rep_password_resets',
            'expire' => 60,
        ],
    ],
];
