<?php

declare(strict_types=1);

namespace Proovit\BotFilter\Support;

use Proovit\BotFilter\Enums\BotProbeClassification;

final class BotProbeDefinition
{
    /**
     * @param array<int, string> $match
     */
    public function __construct(
        public readonly string $key,
        public readonly string $label,
        public readonly array $match = [],
        public readonly BotProbeClassification $suggestedClassification = BotProbeClassification::Bot,
        public readonly bool $enabled = true,
        public readonly bool $notify = false,
    ) {
    }

    public static function fromArray(array $definition): self
    {
        return new self(
            key: (string) ($definition['key'] ?? $definition['label'] ?? 'probe'),
            label: (string) ($definition['label'] ?? $definition['key'] ?? __('laravel-bot-filter::laravel-bot-filter.common.probe')),
            match: array_values(array_filter(array_map(static fn ($value) => (string) $value, (array) ($definition['match'] ?? [])))),
            suggestedClassification: BotProbeClassification::tryFrom((string) ($definition['suggested_classification'] ?? 'bot')) ?? BotProbeClassification::Bot,
            enabled: (bool) ($definition['enabled'] ?? true),
            notify: (bool) ($definition['notify'] ?? false),
        );
    }

    public function matches(string $normalizedPath): bool
    {
        $path = ltrim(mb_strtolower(trim($normalizedPath)), '/');

        foreach ($this->match as $candidate) {
            $candidate = ltrim(mb_strtolower(trim($candidate)), '/');

            if ($candidate === $path) {
                return true;
            }

            if (str_contains($candidate, '*')) {
                $pattern = '#^'.str_replace('\*', '.*', preg_quote($candidate, '#')).'$#i';

                if (preg_match($pattern, $path) === 1) {
                    return true;
                }
            }

            if (str_starts_with($path, rtrim($candidate, '*'))) {
                return true;
            }
        }

        return false;
    }
}
