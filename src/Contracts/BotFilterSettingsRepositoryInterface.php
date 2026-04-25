<?php

declare(strict_types=1);

namespace Proovit\BotFilter\Contracts;

use Proovit\BotFilter\Models\BotFilterSetting;

interface BotFilterSettingsRepositoryInterface
{
    public function settings(): BotFilterSetting;

    /**
     * @param  array<string, mixed>  $context
     */
    public function effectiveSettings(array $context = []): BotFilterSetting;

    /**
     * @return array<int, array<string, mixed>>
     */
    public function rulesets(): array;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(array $attributes): BotFilterSetting;

    public function reset(): BotFilterSetting;
}
