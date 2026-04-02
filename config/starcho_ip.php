<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Starcho IP Geolocation Module
    |--------------------------------------------------------------------------
    |
    | Configuración del módulo starcho-ip para capturar y registrar
    | geolocalización de usuarios al registrarse.
    |
    */

    // Interruptor global adicional (el módulo también debe estar activo en DB)
    'enabled' => env('STARCHO_IP_ENABLED', true),

    'provider' => env('STARCHO_IP_PROVIDER', 'ipquery'),

    'providers' => [
        'ipquery' => [
            'url' => 'https://api.ipquery.io/',
            'timeout' => 5,
        ],
        'ipapi' => [
            'url' => 'https://ip-api.com/json/',
            'timeout' => 5,
        ],
    ],

    // TTL de caché en segundos (24 horas por defecto)
    'cache_ttl' => env('STARCHO_IP_CACHE_TTL', 86400),

    // Excluir localhost del registro remoto
    'exclude_localhost' => env('STARCHO_IP_EXCLUDE_LOCALHOST', true),

    // Cuando la IP sea localhost, guardar un registro local de desarrollo
    'capture_localhost' => env('STARCHO_IP_CAPTURE_LOCALHOST', true),

    // Excluir IPs privadas del registro
    'exclude_private_ips' => env('STARCHO_IP_EXCLUDE_PRIVATE_IPS', true),

    // Queue para procesar trabajos async
    'queue' => env('STARCHO_IP_QUEUE', 'default'),

    // Por defecto se procesa en sync para evitar depender de queue worker en local
    'dispatch_async' => env('STARCHO_IP_DISPATCH_ASYNC', false),
];
