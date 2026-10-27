<?php

return [
    'api' => [
        'key'        => (string) env('DADATA_API_KEY', ''),
        'secret_key' => (string) env('DADATA_API_SECRET_KEY', ''),

        'suggestions_url' => env('DADATA_API_URL', 'https://suggestions.dadata.ru/suggestions/api/4_1/rs'),
        'cleaner_url'     => env('DADATA_API_URL', 'https://cleaner.dadata.ru/api/v1/clean'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Настройки HTTP
    |--------------------------------------------------------------------------
    */
    'http' => [
        'debug'           => (bool) env('DADATA_HTTP_DEBUG', false),
        'verify'          => (bool) env('DADATA_HTTP_VERIFY', false),
        'use_retry'       => (bool) env('DADATA_HTTP_USE_RETRY', false),
        'timeout'         => (int) env('DADATA_HTTP_TIMEOUT', 20),
        'connect_timeout' => (int) env('DADATA_HTTP_CONNECT_TIMEOUT', 10),
    ],
];
