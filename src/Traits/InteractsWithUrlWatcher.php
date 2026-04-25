<?php

declare(strict_types=1);

namespace Proovit\UrlWatcher\Traits;

use Illuminate\Http\Request;
use Proovit\UrlWatcher\Models\UrlWatch;
use Proovit\UrlWatcher\UrlWatcher;
use Throwable;

trait InteractsWithUrlWatcher
{
    protected function urlWatcher(): UrlWatcher
    {
        return app(UrlWatcher::class);
    }

    protected function botFilter(): UrlWatcher
    {
        return $this->urlWatcher();
    }

    protected function recordUrlWatch(Request $request, Throwable $throwable): UrlWatch
    {
        return $this->urlWatcher()->observe($request, $throwable);
    }
}
