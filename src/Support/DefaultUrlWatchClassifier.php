<?php

declare(strict_types=1);

namespace Proovit\UrlWatcher\Support;

use Proovit\UrlWatcher\Contracts\UrlWatchClassifierInterface;

final class DefaultUrlWatchClassifier implements UrlWatchClassifierInterface
{
    public function __construct(private readonly UrlWatchCatalog $catalog) {}

    public function match(string $normalizedPath): ?UrlWatchDefinition
    {
        return $this->catalog->match($normalizedPath);
    }
}
