<?php

declare(strict_types=1);

namespace Proovit\BotFilter\Support;

use Proovit\BotFilter\Models\BotProbe;

final class BotProbeObservation
{
    public function __construct(
        public readonly BotProbe $probe,
        public readonly bool $created,
        public readonly ?BotProbeDefinition $definition = null,
    ) {}

    public function isFirstSeen(): bool
    {
        return $this->created;
    }
}
