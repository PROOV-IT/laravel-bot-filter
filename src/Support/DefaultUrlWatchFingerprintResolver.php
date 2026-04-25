<?php

declare(strict_types=1);

namespace Proovit\UrlWatcher\Support;

use Proovit\UrlWatcher\Contracts\UrlWatchFingerprintResolverInterface;

final class DefaultUrlWatchFingerprintResolver implements UrlWatchFingerprintResolverInterface
{
    public function resolve(array $context): string
    {
        $parts = [
            strtolower((string) ($context['exception_class'] ?? 'no-exception')),
            strtolower((string) ($context['host'] ?? 'no-host')),
            strtolower((string) ($context['method'] ?? 'GET')),
            strtolower((string) ($context['normalized_path'] ?? ($context['path'] ?? 'no-path'))),
            strtolower((string) ($context['panel'] ?? 'no-panel')),
        ];

        return hash('sha256', implode('|', $parts));
    }
}
