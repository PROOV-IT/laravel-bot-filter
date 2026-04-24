<?php

declare(strict_types=1);

namespace Proovit\BotFilter\Events;

use Proovit\BotFilter\Support\BotProbeObservation;

final class BotProbeRecorded
{
    public function __construct(public readonly BotProbeObservation $observation)
    {
    }
}
