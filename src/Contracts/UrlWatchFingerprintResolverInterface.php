<?php

declare(strict_types=1);

namespace Proovit\UrlWatcher\Contracts;

interface UrlWatchFingerprintResolverInterface
{
    public function resolve(array $context): string;
}
