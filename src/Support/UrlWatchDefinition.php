<?php

declare(strict_types=1);

namespace Proovit\UrlWatcher\Support;

use Proovit\UrlWatcher\Enums\UrlWatchClassification;

final class UrlWatchDefinition
{
    /**
     * @param  array<int, string>  $match
     */
    public function __construct(
        public readonly string $key,
        public readonly string $label,
        public readonly array $match = [],
        public readonly UrlWatchClassification $suggestedClassification = UrlWatchClassification::Bot,
        public readonly bool $enabled = true,
        public readonly bool $notify = false,
    ) {}

    public static function fromArray(array $definition): self
    {
        return new self(
            key: (string) ($definition['key'] ?? $definition['label'] ?? 'url-watch'),
            label: (string) ($definition['label'] ?? $definition['key'] ?? __('laravel-url-watcher::laravel-url-watcher.common.watch')),
            match: array_values(array_filter(array_map(static fn ($value) => (string) $value, (array) ($definition['match'] ?? [])))),
            suggestedClassification: UrlWatchClassification::tryFrom((string) ($definition['suggested_classification'] ?? 'bot')) ?? UrlWatchClassification::Bot,
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
