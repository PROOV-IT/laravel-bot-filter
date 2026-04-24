<?php

declare(strict_types=1);

namespace Proovit\BotFilter\Support;

use Proovit\BotFilter\Contracts\BotProbeFingerprintResolverInterface;

final class DefaultBotProbeFingerprintResolver implements BotProbeFingerprintResolverInterface
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
