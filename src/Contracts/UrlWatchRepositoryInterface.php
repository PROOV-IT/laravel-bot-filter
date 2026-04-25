<?php

declare(strict_types=1);

namespace Proovit\UrlWatcher\Contracts;

use Proovit\UrlWatcher\Models\UrlWatch;
use Proovit\UrlWatcher\Support\UrlWatchDefinition;
use Proovit\UrlWatcher\Support\UrlWatchObservation;

interface UrlWatchRepositoryInterface
{
    public function record(array $context, ?UrlWatchDefinition $definition = null): UrlWatchObservation;

    public function findByFingerprint(string $fingerprint): ?UrlWatch;
}
