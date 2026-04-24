<?php

declare(strict_types=1);

namespace Proovit\BotFilter\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Proovit\BotFilter\Enums\BotProbeClassification;
use Proovit\BotFilter\Enums\BotProbeStatus;

class BotProbe extends Model
{
    use HasUuids;

    protected $guarded = [];

    public $incrementing = false;

    protected $keyType = 'string';

    protected $casts = [
        'meta' => 'array',
        'classification' => BotProbeClassification::class,
        'suggested_classification' => BotProbeClassification::class,
        'status' => BotProbeStatus::class,
        'first_seen_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'notified_at' => 'datetime',
    ];

    public function getTable(): string
    {
        return (string) config('bot-filter.database.table', 'bot_probes');
    }

    public function getConnectionName(): ?string
    {
        return config('bot-filter.database.connection') ?: parent::getConnectionName();
    }

    public function markAsBot(): self
    {
        $this->classification = BotProbeClassification::Bot;
        $this->status = BotProbeStatus::Reviewed;
        $this->save();

        return $this;
    }

    public function markAsNormal(): self
    {
        $this->classification = BotProbeClassification::Normal;
        $this->status = BotProbeStatus::Reviewed;
        $this->save();

        return $this;
    }

    public function markAsIgnored(): self
    {
        $this->classification = BotProbeClassification::Ignored;
        $this->status = BotProbeStatus::Archived;
        $this->save();

        return $this;
    }

    public function markAsPending(): self
    {
        $this->classification = BotProbeClassification::Unknown;
        $this->status = BotProbeStatus::Pending;
        $this->save();

        return $this;
    }

    public function markAsNotified(?Carbon $at = null): self
    {
        $this->status = BotProbeStatus::Notified;
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
        return $this->status === BotProbeStatus::Pending
            && blank($this->notified_at)
            && (bool) config('bot-filter.notification.enabled', true);
    }
}
