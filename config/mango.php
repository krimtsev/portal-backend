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
        'verify'          => (bool) env('MANGO_HTTP_VERIFY', false),
        'use_retry'       => (bool) env('MANGO_HTTP_USE_RETRY', false),
        'timeout'         => (int) env('MANGO_HTTP_TIMEOUT', 30),
        'connect_timeout' => (int) env('MANGO_HTTP_CONNECT_TIMEOUT', 10),
    ],
];
