<?php

return [
    'defaults' => [
        'guard' => 'api',
        'passwords' => 'users',
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
    ]
];
