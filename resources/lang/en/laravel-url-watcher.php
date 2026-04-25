<?php

return [
    'common' => [
        'watch' => 'URL watch',
    ],
    'enums' => [
        'classification' => [
            'unknown' => 'Unknown',
            'bot' => 'Bot',
            'normal' => 'Normal',
            'ignored' => 'Ignored',
            'whitelisted' => 'Whitelisted',
        ],
        'status' => [
            'pending' => 'Pending',
            'notified' => 'Notified',
            'reviewed' => 'Reviewed',
            'archived' => 'Archived',
        ],
    ],
    'notifications' => [
        'url_watch_detected' => [
            'subject' => 'URL watch detected: :path',
            'title' => 'A new URL watch incident was recorded.',
            'path' => 'Path: :path',
            'host' => 'Host: :host',
            'count' => 'Count: :count',
        ],
        'url_watch_digest' => [
            'subject' => 'URL watcher digest',
            'period' => 'Digest window: :hours hour(s)',
            'total' => 'Total URL watches: :count',
            'pending' => 'Pending: :count',
            'reviewed' => 'Reviewed: :count',
            'bots' => 'Bot classified: :count',
            'normal' => 'Normal classified: :count',
            'ignored' => 'Ignored: :count',
        ],
    ],
];
