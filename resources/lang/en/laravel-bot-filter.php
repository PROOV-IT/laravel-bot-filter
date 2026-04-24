<?php

return [
    'common' => [
        'probe' => 'Probe',
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
        'bot_probe_detected' => [
            'subject' => 'Bot probe detected: :path',
            'title' => 'A new bot/probe incident was recorded.',
            'path' => 'Path: :path',
            'host' => 'Host: :host',
            'count' => 'Count: :count',
        ],
    ],
];
