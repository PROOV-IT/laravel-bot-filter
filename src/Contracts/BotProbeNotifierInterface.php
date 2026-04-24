<?php

declare(strict_types=1);

namespace Proovit\BotFilter\Contracts;

use Proovit\BotFilter\Models\BotProbe;

interface BotProbeNotifierInterface
{
    public function notify(BotProbe $probe): void;
}
