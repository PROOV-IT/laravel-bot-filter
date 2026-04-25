<?php

declare(strict_types=1);

namespace Proovit\UrlWatcher\Events;

use Proovit\UrlWatcher\Models\UrlWatch;

final class UrlWatchClassified
{
    public function __construct(public readonly UrlWatch $watch) {}
}
