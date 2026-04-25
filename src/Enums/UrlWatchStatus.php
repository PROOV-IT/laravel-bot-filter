<?php

declare(strict_types=1);

namespace Proovit\UrlWatcher\Enums;

enum UrlWatchStatus: string
{
    case Pending = 'pending';
    case Notified = 'notified';
    case Reviewed = 'reviewed';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Pending => __('laravel-url-watcher::laravel-url-watcher.enums.status.pending'),
            self::Notified => __('laravel-url-watcher::laravel-url-watcher.enums.status.notified'),
            self::Reviewed => __('laravel-url-watcher::laravel-url-watcher.enums.status.reviewed'),
            self::Archived => __('laravel-url-watcher::laravel-url-watcher.enums.status.archived'),
        };
    }
}
