<?php

declare(strict_types=1);

namespace Proovit\UrlWatcher\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Proovit\UrlWatcher\Enums\UrlWatchClassification;
use Proovit\UrlWatcher\Enums\UrlWatchStatus;

class UrlWatch extends Model
{
    use HasUuids;

    protected $guarded = [];

    public $incrementing = false;

    protected $keyType = 'string';

    protected $casts = [
        'meta' => 'array',
        'classification' => UrlWatchClassification::class,
        'suggested_classification' => UrlWatchClassification::class,
        'status' => UrlWatchStatus::class,
        'first_seen_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'notified_at' => 'datetime',
    ];

    public function getTable(): string
    {
        return (string) config('url-watcher.database.table', 'url_watches');
    }

    public function getConnectionName(): ?string
    {
        return config('url-watcher.database.connection') ?: parent::getConnectionName();
    }

    public function markAsBot(): self
    {
        $this->classification = UrlWatchClassification::Bot;
        $this->status = UrlWatchStatus::Reviewed;
        $this->save();

        return $this;
    }

    public function markAsNormal(): self
    {
        $this->classification = UrlWatchClassification::Normal;
        $this->status = UrlWatchStatus::Reviewed;
        $this->save();

        return $this;
    }

    public function markAsIgnored(): self
    {
        $this->classification = UrlWatchClassification::Ignored;
        $this->status = UrlWatchStatus::Archived;
        $this->save();

        return $this;
    }

    public function markAsPending(): self
    {
        $this->classification = UrlWatchClassification::Unknown;
        $this->status = UrlWatchStatus::Pending;
        $this->save();

        return $this;
    }

    public function markAsNotified(?Carbon $at = null): self
    {
        $this->status = UrlWatchStatus::Notified;
        $this->notified_at = $at ?? now();
        $this->save();

        return $this;
    }

    public function touchHit(array $meta = []): self
    {
        $this->count = (int) $this->count + 1;
        $this->last_seen_at = now();
        $this->meta = array_replace_recursive($this->meta ?? [], $meta);
        $this->save();

        return $this;
    }

    public function shouldNotify(): bool
    {
        return $this->status === UrlWatchStatus::Pending
            && blank($this->notified_at)
            && (bool) config('url-watcher.notification.enabled', true);
    }

    public function events(): HasMany
    {
        return $this->hasMany(UrlWatchEvent::class, 'url_watch_id');
    }
}
