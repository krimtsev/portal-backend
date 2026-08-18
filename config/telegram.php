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
    | Настройки debug (Перехват сообщений)
    |--------------------------------------------------------------------------
    */
    'debug' => [
        'enabled' => (bool) env('TELEGRAM_DEBUG_ENABLED', false),
        'chat_id' => env('TELEGRAM_DEBUG_CHAT_ID'),
        'bot'     => env('TELEGRAM_DEBUG_BOT', 'debug'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Настройки ботов (Мультитокен)
    |--------------------------------------------------------------------------
    */
    'bots' => [
        'main' => [
            'token' => env('TELEGRAM_MAIN_BOT_TOKEN'),
        ],
        'notification' => [
            'token' => env('TELEGRAM_NOTIFICATION_BOT_TOKEN'),
        ],
        'monitoring' => [
            'token' => env('TELEGRAM_MONITORING_BOT_TOKEN'),
        ],
        'debug' => [
            'token' => env('TELEGRAM_DEBUG_BOT_TOKEN'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Целевые каналы и чаты (Channels / Target Chats)
    |--------------------------------------------------------------------------
    */
    'channels' => [
        'staff_updates' => [
            'chat_id' => env('TELEGRAM_STAFF_UPDATES_CHAT_ID'),
            'bot'     => 'notification',
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
    | Настройки ограничения очереди задач
    |--------------------------------------------------------------------------
    */
    'queue' => [
        'throttle' => [
            'enabled' => (bool) env('TELEGRAM_QUEUE_THROTTLE_ENABLED', false),
            'sleep'   => (float) env('TELEGRAM_QUEUE_THROTTLE_SLEEP', 1.0),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Настройки HTTP
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

        'scheme' => env('TELEGRAM_PROXY_SCHEME', 'socks5h'),

        'type'   => env('TELEGRAM_PROXY_TYPE', 'socks5_hostname'),

        'username' => env('TELEGRAM_PROXY_USER'),
        'password' => env('TELEGRAM_PROXY_PASS'),

        'list' => [
            '87.236.146.48:8678',
            '85.137.166.136:8678',
        ],
    ],
];
