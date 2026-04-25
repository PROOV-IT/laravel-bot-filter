<?php

declare(strict_types=1);

namespace Proovit\UrlWatcher\Support;

use Proovit\UrlWatcher\Models\UrlWatch;
use Proovit\UrlWatcher\Models\UrlWatchEvent;

final class UrlWatchObservation
{
    public function __construct(
        public readonly UrlWatch $watch,
        public readonly UrlWatchEvent $event,
        public readonly bool $created,
        public readonly ?UrlWatchDefinition $definition = null
    ) {}

    public function isFirstSeen(): bool
    {
        return $this->created;
    }
}
