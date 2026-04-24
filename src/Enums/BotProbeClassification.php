<?php

declare(strict_types=1);

namespace Proovit\BotFilter\Enums;

enum BotProbeClassification: string
{
    case Unknown = 'unknown';
    case Bot = 'bot';
    case Normal = 'normal';
    case Ignored = 'ignored';
    case Whitelisted = 'whitelisted';

    public function label(): string
    {
        return match ($this) {
            self::Unknown => __('laravel-bot-filter::laravel-bot-filter.enums.classification.unknown'),
            self::Bot => __('laravel-bot-filter::laravel-bot-filter.enums.classification.bot'),
            self::Normal => __('laravel-bot-filter::laravel-bot-filter.enums.classification.normal'),
            self::Ignored => __('laravel-bot-filter::laravel-bot-filter.enums.classification.ignored'),
            self::Whitelisted => __('laravel-bot-filter::laravel-bot-filter.enums.classification.whitelisted'),
        };
    }
}
