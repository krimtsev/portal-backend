<?php

return [
    'app_token'     => env('YCLIENTS_APP_TOKEN', ''),
    'partner_token' => env('YCLIENTS_PARTNER_TOKEN', ''),

    /*
    |--------------------------------------------------------------------------
    | Настройки ограничения очереди задач
    |--------------------------------------------------------------------------
    */
    'queue' => [
        'throttle' => [
            'enabled' => (bool) env('YCLIENTS_QUEUE_THROTTLE_ENABLED', false),
            'sleep' => (float) env('YCLIENTS_QUEUE_THROTTLE_SLEEP', 1.0),
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Настройки HTTP
    |--------------------------------------------------------------------------
    */
    'http' => [
        'debug'           => (bool) env('YCLIENTS_HTTP_DEBUG', false),
        'verify'          => (bool) env('YCLIENTS_HTTP_VERIFY', false),
        'use_retry'       => (bool) env('YCLIENTS_HTTP_USE_RETRY', false),
        'timeout'         => (int) env('YCLIENTS_HTTP_TIMEOUT', 30),
        'connect_timeout' => (int) env('YCLIENTS_HTTP_CONNECT_TIMEOUT', 10),
    ],
];
