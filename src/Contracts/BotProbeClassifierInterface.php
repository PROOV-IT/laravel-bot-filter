<?php

declare(strict_types=1);

namespace Proovit\BotFilter\Contracts;

use Proovit\BotFilter\Support\BotProbeDefinition;

interface BotProbeClassifierInterface
{
    public function match(string $normalizedPath): ?BotProbeDefinition;
}
