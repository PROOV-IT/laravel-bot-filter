<?php

declare(strict_types=1);

namespace Proovit\BotFilter\Events;

use Proovit\BotFilter\Models\BotProbe;

final class BotProbeNotificationSent
{
    public function __construct(public readonly BotProbe $probe)
    {
    }
}
