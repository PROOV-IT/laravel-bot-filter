<?php

return [
    'enabled' => env('BOT_FILTER_ENABLED', true),

    'database' => [
        'connection' => env('BOT_FILTER_DB_CONNECTION'),
        'table' => env('BOT_FILTER_TABLE', 'bot_probes'),
    ],

    'settings' => [
        'connection' => env('BOT_FILTER_SETTINGS_DB_CONNECTION'),
        'table' => env('BOT_FILTER_SETTINGS_TABLE', 'bot_filter_settings'),
    ],

    'notification' => [
        'enabled' => env('BOT_FILTER_NOTIFY_ENABLED', true),
        'mode' => env('BOT_FILTER_NOTIFY_MODE', 'default'),
        'mail' => env('BOT_FILTER_NOTIFY_MAIL', env('APP_ADMIN_EMAIL', 'contact@proov-it.io')),
        'route' => env('BOT_FILTER_NOTIFY_ROUTE', null),
        'custom_notification_class' => env('BOT_FILTER_CUSTOM_NOTIFICATION_CLASS', null),
    ],

    'defaults' => [
        'notify_first_seen' => env('BOT_FILTER_NOTIFY_FIRST_SEEN', true),
        'track_request_headers' => env('BOT_FILTER_TRACK_HEADERS', true),
        'track_request_payload' => env('BOT_FILTER_TRACK_PAYLOAD', true),
    ],

    'capture' => [
        'enabled' => env('BOT_FILTER_CAPTURE_ENABLED', true),
        'exceptions' => env('BOT_FILTER_CAPTURE_EXCEPTIONS', true),
        'statuses' => [404, 405],
        'ignore' => [
            'paths' => [],
            'hosts' => [],
            'panels' => [],
            'methods' => [],
            'exception_classes' => [],
        ],
    ],

    'probes' => [
        [
            'key' => 'robots-txt',
            'label' => 'robots.txt',
            'match' => ['robots.txt', '/robots.txt'],
            'suggested_classification' => 'bot',
            'enabled' => true,
            'notify' => false,
        ],
        [
            'key' => 'favicon-ico',
            'label' => 'favicon.ico',
            'match' => ['favicon.ico', '/favicon.ico'],
            'suggested_classification' => 'bot',
            'enabled' => true,
            'notify' => false,
        ],
        [
            'key' => 'php-probe',
            'label' => 'PHP probe',
            'match' => [
                'php.ini',
                'phpinfo',
                'phpinfo.php',
                'info.php',
            ],
            'suggested_classification' => 'bot',
            'enabled' => true,
            'notify' => false,
        ],
        [
            'key' => 'wordpress-scan',
            'label' => 'WordPress scan',
            'match' => [
                'wp-login.php',
                'wp-admin',
                'wp-config.php',
                'xmlrpc.php',
                'wp-json',
            ],
            'suggested_classification' => 'bot',
            'enabled' => true,
            'notify' => false,
        ],
        [
            'key' => 'settings-probe',
            'label' => 'Settings probe',
            'match' => [
                'settings.ini',
                '.env',
                '.env.bak',
                '.env.local',
            ],
            'suggested_classification' => 'bot',
            'enabled' => true,
            'notify' => false,
        ],
    ],
];
