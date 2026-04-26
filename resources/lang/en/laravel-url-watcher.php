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
            'events_total' => 'Total events: :count',
            'events_delta' => 'Event delta vs previous window: :count (previous: :previous)',
            'unique_hosts' => 'Unique hosts: :count',
            'new_watches' => 'New watches in window: :count',
            'write_attempts' => 'Write attempts: :count',
            'client_errors' => 'Client errors: :count',
            'server_errors' => 'Server errors: :count',
            'pending' => 'Pending: :count',
            'reviewed' => 'Reviewed: :count',
            'bots' => 'Bot classified: :count',
            'normal' => 'Normal classified: :count',
            'ignored' => 'Ignored: :count',
            'largest_host_spike' => 'Largest host spike: :label (current: :current, previous: :previous, delta: :delta)',
            'top_paths_heading' => 'Top paths',
            'top_hosts_heading' => 'Top hosts',
            'top_methods_heading' => 'Top methods',
            'top_statuses_heading' => 'Top HTTP statuses',
            'new_paths_heading' => 'New paths seen in this window',
            'new_path' => ':label',
            'recent_events_heading' => 'Recent events',
            'row' => ':label - :count',
            'recent_event' => ':occurred_at | :method | :host | :path | :status',
        ],
    ],
];
