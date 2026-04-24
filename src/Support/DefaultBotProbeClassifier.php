<?php

declare(strict_types=1);

namespace Proovit\BotFilter\Support;

use Proovit\BotFilter\Contracts\BotProbeClassifierInterface;

final class DefaultBotProbeClassifier implements BotProbeClassifierInterface
{
    public function __construct(private readonly BotProbeCatalog $catalog)
    {
    }

    public function match(string $normalizedPath): ?BotProbeDefinition
    {
        return $this->catalog->match($normalizedPath);
    }
}
