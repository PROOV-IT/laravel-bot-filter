<?php

declare(strict_types=1);

namespace Proovit\BotFilter\Contracts;

interface BotProbeFingerprintResolverInterface
{
    public function resolve(array $context): string;
}
