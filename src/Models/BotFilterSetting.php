<?php

declare(strict_types=1);

namespace Proovit\BotFilter\Models;

use Illuminate\Database\Eloquent\Model;

class BotFilterSetting extends Model
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
        'show_widgets' => 'bool',
    ];

    public function getTable(): string
    {
        return (string) config('bot-filter.settings.table', 'bot_filter_settings');
    }

    public function getConnectionName(): ?string
    {
        return config('bot-filter.settings.connection') ?: parent::getConnectionName();
    }
}
