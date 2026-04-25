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
        'title' => env('BOT_FILTER_NOTIFY_TITLE', null),
        'intro' => env('BOT_FILTER_NOTIFY_INTRO', null),
        'mail' => env('BOT_FILTER_NOTIFY_MAIL', env('APP_ADMIN_EMAIL', 'contact@proov-it.io')),
        'route' => env('BOT_FILTER_NOTIFY_ROUTE', null),
        'custom_notification_class' => env('BOT_FILTER_CUSTOM_NOTIFICATION_CLASS', null),
    ],

    'rulesets' => [
        [
            'key' => 'production',
            'label' => 'Production',
            'enabled' => false,
            'environments' => ['production'],
            'hosts' => ['*.proov-it.online'],
            'overrides' => [
                'notifications_enabled' => true,
                'notification_title' => 'Production bot probe',
                'notification_intro' => "A production probe was detected.\nPlease review it from the admin cockpit.",
            ],
        ],
        [
            'key' => 'staging',
            'label' => 'Staging',
            'enabled' => false,
            'environments' => ['staging', 'testing'],
            'hosts' => ['*.staging.proov-it.online'],
            'overrides' => [
                'notifications_enabled' => true,
                'notification_title' => 'Staging bot probe',
            ],
        ],
    ],

    'digest' => [
        'enabled' => env('BOT_FILTER_DIGEST_ENABLED', false),
        'mail' => env('BOT_FILTER_DIGEST_MAIL', env('APP_ADMIN_EMAIL', 'contact@proov-it.io')),
        'title' => env('BOT_FILTER_DIGEST_TITLE', 'Bot probes digest'),
        'intro' => env('BOT_FILTER_DIGEST_INTRO', 'Here is the bot probes digest for the selected window.'),
        'window_hours' => env('BOT_FILTER_DIGEST_WINDOW_HOURS', 24),
        'notify_when_empty' => env('BOT_FILTER_DIGEST_NOTIFY_WHEN_EMPTY', false),
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
