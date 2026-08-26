<?php

return [

    'defaults' => [
        'guard' => 'web',
        'passwords' => 'pegawais',
    ],

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'pegawais',
        ],
    ],

    'providers' => [
        'pegawais' => [
            'driver' => 'eloquent',
            'model' => App\Models\Pegawai::class,
        ],
    ],

    'passwords' => [
        'pegawais' => [
            'provider' => 'pegawais',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => 10800,

];
