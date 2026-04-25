<?php

declare(strict_types=1);

namespace Proovit\UrlWatcher\Contracts;

use Proovit\UrlWatcher\Support\UrlWatchDefinition;

interface UrlWatchClassifierInterface
{
    public function match(string $normalizedPath): ?UrlWatchDefinition;
}
