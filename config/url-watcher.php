<?php

return [
    'enabled' => env('URL_WATCHER_ENABLED', true),

    'database' => [
        'connection' => env('URL_WATCHER_DB_CONNECTION'),
        'table' => env('URL_WATCHER_TABLE', 'url_watches'),
        'events_table' => env('URL_WATCHER_EVENTS_TABLE', 'url_watch_events'),
    ],

    'settings' => [
        'connection' => env('URL_WATCHER_SETTINGS_DB_CONNECTION'),
        'table' => env('URL_WATCHER_SETTINGS_TABLE', 'url_watcher_settings'),
    ],

    'notification' => [
        'enabled' => env('URL_WATCHER_NOTIFY_ENABLED', true),
        'mode' => env('URL_WATCHER_NOTIFY_MODE', 'default'),
        'title' => env('URL_WATCHER_NOTIFY_TITLE', null),
        'intro' => env('URL_WATCHER_NOTIFY_INTRO', null),
        'mail' => env('URL_WATCHER_NOTIFY_MAIL', env('APP_ADMIN_EMAIL', 'contact@proov-it.io')),
        'route' => env('URL_WATCHER_NOTIFY_ROUTE', null),
        'custom_notification_class' => env('URL_WATCHER_CUSTOM_NOTIFICATION_CLASS', null),
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
                'notification_title' => 'Production URL watch alert',
                'notification_intro' => "A suspicious URL event was detected in production.\nPlease review it from the admin cockpit.",
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
                'notification_title' => 'Staging URL watch alert',
            ],
        ],
    ],

    'digest' => [
        'enabled' => env('URL_WATCHER_DIGEST_ENABLED', false),
        'mail' => env('URL_WATCHER_DIGEST_MAIL', env('APP_ADMIN_EMAIL', 'contact@proov-it.io')),
        'title' => env('URL_WATCHER_DIGEST_TITLE', 'URL watcher digest'),
        'intro' => env('URL_WATCHER_DIGEST_INTRO', 'Here is the URL watcher digest for the selected window.'),
        'window_hours' => env('URL_WATCHER_DIGEST_WINDOW_HOURS', 24),
        'notify_when_empty' => env('URL_WATCHER_DIGEST_NOTIFY_WHEN_EMPTY', false),
        'recent_events_limit' => env('URL_WATCHER_DIGEST_RECENT_EVENTS_LIMIT', 10),
    ],

    'retention' => [
        'enabled' => env('URL_WATCHER_RETENTION_ENABLED', false),
        'days' => env('URL_WATCHER_RETENTION_DAYS', 30),
        'prune_aggregates' => env('URL_WATCHER_RETENTION_PRUNE_AGGREGATES', false),
    ],

    'defaults' => [
        'notify_first_seen' => env('URL_WATCHER_NOTIFY_FIRST_SEEN', true),
        'track_request_headers' => env('URL_WATCHER_TRACK_HEADERS', true),
        'track_request_payload' => env('URL_WATCHER_TRACK_PAYLOAD', true),
    ],

    'capture' => [
        'enabled' => env('URL_WATCHER_CAPTURE_ENABLED', true),
        'exceptions' => env('URL_WATCHER_CAPTURE_EXCEPTIONS', true),
        'statuses' => [404, 405],
        'ignore' => [
            'paths' => [],
            'hosts' => [],
            'panels' => [],
            'methods' => [],
            'exception_classes' => [],
        ],
    ],

    'definitions' => [
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

    'history' => [
        'enabled' => env('URL_WATCHER_HISTORY_ENABLED', true),
        'store_user_agent' => env('URL_WATCHER_HISTORY_STORE_USER_AGENT', true),
        'store_query_string' => env('URL_WATCHER_HISTORY_STORE_QUERY_STRING', true),
        'store_referer' => env('URL_WATCHER_HISTORY_STORE_REFERER', true),
    ],
];
