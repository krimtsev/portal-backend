<?php

return [
    'api' => [
        'key'  => (string) env('MANGO_VPBX_API_KEY', ''),
        'salt' => (string) env('MANGO_SALT', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | Настройки HTTP
    |--------------------------------------------------------------------------
    */
    'http' => [
        'debug'           => (bool) env('MANGO_HTTP_DEBUG', false),
        'verify'          => (bool) env('MANGO_HTTP_VERIFY', false),
        'use_retry'       => (bool) env('MANGO_HTTP_USE_RETRY', false),
        'timeout'         => (int) env('MANGO_HTTP_TIMEOUT', 30),
        'connect_timeout' => (int) env('MANGO_HTTP_CONNECT_TIMEOUT', 10),
    ],

    /*
    |--------------------------------------------------------------------------
    | Настройки часового пояса ВАТС
    |--------------------------------------------------------------------------
    */
    'timezone' => (string) env('MANGO_TIMEZONE', 'Europe/Moscow'),

    /*
    |--------------------------------------------------------------------------
    | Настройки получения статистики звонков
    |--------------------------------------------------------------------------
    */
    'call_stats_delay' => (int) env('MANGO_CALL_STATS_DELAY', 35),
];
