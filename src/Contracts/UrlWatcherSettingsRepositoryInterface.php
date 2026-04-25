<?php

declare(strict_types=1);

namespace Proovit\UrlWatcher\Contracts;

use Proovit\UrlWatcher\Models\UrlWatcherSetting;

interface UrlWatcherSettingsRepositoryInterface
{
    public function settings(): UrlWatcherSetting;

    /**
     * @param  array<string, mixed>  $context
     */
    public function effectiveSettings(array $context = []): UrlWatcherSetting;

    /**
     * @return array<int, array<string, mixed>>
     */
    public function rulesets(): array;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(array $attributes): UrlWatcherSetting;

    public function reset(): UrlWatcherSetting;
}
