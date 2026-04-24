<?php

declare(strict_types=1);

namespace Proovit\BotFilter\Enums;

enum BotProbeStatus: string
{
    case Pending = 'pending';
    case Notified = 'notified';
    case Reviewed = 'reviewed';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Pending => __('laravel-bot-filter::laravel-bot-filter.enums.status.pending'),
            self::Notified => __('laravel-bot-filter::laravel-bot-filter.enums.status.notified'),
            self::Reviewed => __('laravel-bot-filter::laravel-bot-filter.enums.status.reviewed'),
            self::Archived => __('laravel-bot-filter::laravel-bot-filter.enums.status.archived'),
        };
    }
}
