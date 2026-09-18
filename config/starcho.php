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

    'live' => [
        // Use ws:// or wss:// for WebSocket, http(s):// for Server-Sent Events.
        // Leave empty until the live transport is configured for the deployment.
        'endpoint' => env('STARCHO_LIVE_ENDPOINT'),
        'protocol' => env('STARCHO_LIVE_PROTOCOL', 'auto'),
        'reconnect_base_ms' => (int) env('STARCHO_LIVE_RECONNECT_BASE_MS', 1000),
        'reconnect_max_ms' => (int) env('STARCHO_LIVE_RECONNECT_MAX_MS', 30000),
    ],
];
