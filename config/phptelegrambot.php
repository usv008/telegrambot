<?php

declare(strict_types=1);

return [
    /**
     * Bot configuration
     */
    'bot'      => [
        'name'    => env('PHP_TELEGRAM_BOT_NAME', ''),
        'api_key' => env('PHP_TELEGRAM_BOT_API_KEY', ''),
    ],

    /**
     * Database integration
     */
    'database' => [
        'enabled'    => true,
        'connection' => env('DB_CONNECTION', 'mysql_bot'),
        'prefix'     => env('PHP_TELEGRAM_BOT_TABLE_PREFIX', ''),
    ],

    'commands' => [
        'before'  => true,
        'paths'   => [
            // Custom command paths
            app_path('Telegram/Commands')
        ],
        'configs' => [
            // Custom commands configs
        ],
    ],

    'admins'  => [
        // Admin ids
        522750680
    ],

    /**
     * Request limiter
     */
    'limiter' => [
        'enabled'  => false,
        'interval' => 1,
    ],

    'upload_path'   => env('PHP_TELEGRAM_BOT_UPLOAD_PATH', ''),
    'download_path' => env('PHP_TELEGRAM_BOT_DOWNLOAD_PATH', ''),
];
