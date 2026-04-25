<?php

declare(strict_types=1);

namespace Proovit\UrlWatcher\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UrlWatchEvent extends Model
{
    use HasUuids;

    protected $guarded = [];

    public $incrementing = false;

    protected $keyType = 'string';

    protected $casts = [
        'meta' => 'array',
        'occurred_at' => 'datetime',
    ];

    public function getTable(): string
    {
        return (string) config('url-watcher.database.events_table', 'url_watch_events');
    }

    public function getConnectionName(): ?string
    {
        return config('url-watcher.database.connection') ?: parent::getConnectionName();
    }

    public function urlWatch(): BelongsTo
    {
        return $this->belongsTo(UrlWatch::class, 'url_watch_id');
    }
}
