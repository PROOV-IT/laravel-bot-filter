<?php

declare(strict_types=1);

namespace Proovit\BotFilter\Events;

final class BotProbeIgnored
{
    /**
     * @param array<string, mixed> $context
     */
    public function __construct(
        public readonly string $normalizedPath,
        public readonly string $reason,
        public readonly array $context = [],
    ) {
    }
}
