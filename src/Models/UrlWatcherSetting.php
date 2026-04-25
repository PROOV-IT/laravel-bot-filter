<?php

declare(strict_types=1);

namespace Proovit\UrlWatcher\Models;

use Illuminate\Database\Eloquent\Model;

class UrlWatcherSetting extends Model
{
    protected $guarded = [];

    protected $casts = [
        'capture_enabled' => 'bool',
        'capture_exceptions' => 'bool',
        'capture_statuses' => 'array',
        'ignore_paths' => 'array',
        'ignore_hosts' => 'array',
        'ignore_panels' => 'array',
        'ignore_methods' => 'array',
        'ignore_exception_classes' => 'array',
        'notifications_enabled' => 'bool',
        'active_ruleset' => 'string',
        'digest_enabled' => 'bool',
        'digest_mail' => 'string',
        'digest_title' => 'string',
        'digest_intro' => 'string',
        'digest_window_hours' => 'int',
        'digest_notify_when_empty' => 'bool',
        'digest_recent_events_limit' => 'int',
        'retention_enabled' => 'bool',
        'retention_days' => 'int',
        'retention_prune_aggregates' => 'bool',
        'show_widgets' => 'bool',
    ];

    public function getTable(): string
    {
        return (string) config('url-watcher.settings.table', 'url_watcher_settings');
    }

    public function getConnectionName(): ?string
    {
        return config('url-watcher.settings.connection') ?: parent::getConnectionName();
    }
}
