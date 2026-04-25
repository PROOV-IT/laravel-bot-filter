<?php

declare(strict_types=1);

namespace Proovit\UrlWatcher\Contracts;

use Proovit\UrlWatcher\Models\UrlWatch;

interface UrlWatchNotifierInterface
{
    public function notify(UrlWatch $watch): void;
}
