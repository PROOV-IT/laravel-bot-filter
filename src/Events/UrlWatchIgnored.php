<?php

declare(strict_types=1);

namespace Proovit\UrlWatcher\Events;

final class UrlWatchIgnored
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function __construct(
        public readonly string $normalizedPath,
        public readonly string $reason,
        public readonly array $context = [],
    ) {}
}
