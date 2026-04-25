<?php

declare(strict_types=1);

namespace Proovit\UrlWatcher\Enums;

enum UrlWatchClassification: string
{
    case Unknown = 'unknown';
    case Bot = 'bot';
    case Normal = 'normal';
    case Ignored = 'ignored';
    case Whitelisted = 'whitelisted';

    public function label(): string
    {
        return match ($this) {
            self::Unknown => __('laravel-url-watcher::laravel-url-watcher.enums.classification.unknown'),
            self::Bot => __('laravel-url-watcher::laravel-url-watcher.enums.classification.bot'),
            self::Normal => __('laravel-url-watcher::laravel-url-watcher.enums.classification.normal'),
            self::Ignored => __('laravel-url-watcher::laravel-url-watcher.enums.classification.ignored'),
            self::Whitelisted => __('laravel-url-watcher::laravel-url-watcher.enums.classification.whitelisted'),
        };
    }
}
