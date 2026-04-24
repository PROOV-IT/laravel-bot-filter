<?php

declare(strict_types=1);

namespace Proovit\BotFilter\Contracts;

use Proovit\BotFilter\Models\BotProbe;
use Proovit\BotFilter\Support\BotProbeDefinition;
use Proovit\BotFilter\Support\BotProbeObservation;

interface BotProbeRepositoryInterface
{
    public function record(array $context, ?BotProbeDefinition $definition = null): BotProbeObservation;

    public function findByFingerprint(string $fingerprint): ?BotProbe;
}
