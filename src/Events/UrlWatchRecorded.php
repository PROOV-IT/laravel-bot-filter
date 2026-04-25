<?php

declare(strict_types=1);

namespace Proovit\UrlWatcher\Events;

use Proovit\UrlWatcher\Support\UrlWatchObservation;

final class UrlWatchRecorded
{
    public function __construct(public readonly UrlWatchObservation $observation) {}
}
