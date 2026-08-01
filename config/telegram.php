<?php

use App\Integrations\Telegram\Enums\ParseMode;

return [
    /*
    |--------------------------------------------------------------------------
    | Бот по умолчанию
    |--------------------------------------------------------------------------
    */
    'default' => env('TELEGRAM_BOT', 'main'),

    /*
    |--------------------------------------------------------------------------
    | Настройки ботов (Мультитокен)
    |--------------------------------------------------------------------------
    */
    'bots' => [
        'main' => [
            'token' => env('TELEGRAM_MAIN_BOT_TOKEN'),
        ],
        'notifications' => [
            'token' => env('TELEGRAM_NOTIFY_BOT_TOKEN'),
        ],
        'support' => [
            'token' => env('TELEGRAM_SUPPORT_BOT_TOKEN'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Глобальные настройки по умолчанию
    |--------------------------------------------------------------------------
    */
    'defaults' => [
        'parse_mode' => ParseMode::HTML->value,
    ],

    /*
    |--------------------------------------------------------------------------
    | Настройки  умолчанию
    |--------------------------------------------------------------------------
    */
    'http' => [
        'debug'           => (bool) env('TELEGRAM_HTTP_DEBUG', false),
        'verify'          => (bool) env('TELEGRAM_HTTP_VERIFY', true),
        'use_retry'       => (bool) env('TELEGRAM_HTTP_USE_RETRY', false),
        'timeout'         => (int) env('TELEGRAM_HTTP_TIMEOUT', 30),
        'connect_timeout' => (int) env('TELEGRAM_HTTP_CONNECT_TIMEOUT', 10),
    ],

    /*
    |--------------------------------------------------------------------------
    | Настройки Прокси
    |--------------------------------------------------------------------------
    */
    'proxy' => [
        'enabled' => (bool) env('TELEGRAM_PROXY_ENABLED', false),

        'scheme' => env('TELEGRAM_PROXY_SCHEME', 'http'),

        'username' => env('TELEGRAM_PROXY_USER'),
        'password' => env('TELEGRAM_PROXY_PASS'),

        'list' => [
            '87.236.146.48:8678',
            '85.137.166.136:8678',
        ],
    ],
];
