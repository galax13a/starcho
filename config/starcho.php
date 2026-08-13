<?php

return [
    /*
    | The browser installer is opt-in. Enable it only during provisioning and
    | turn it off again after the first successful installation.
    */
    'install_enabled' => (bool) env('STARCHO_INSTALL_ENABLED', false),

    'admin' => [
        'email' => env('STARCHO_ADMIN_EMAIL', 'admin@example.com'),
        'password' => env('STARCHO_ADMIN_PASSWORD'),
    ],

    'install' => [
        'admin_name' => null,
        'admin_email' => null,
        'admin_password' => null,
        'refresh_defaults' => false,
        'reset_admin_password' => false,
    ],
];
