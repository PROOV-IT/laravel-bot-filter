<?php

declare(strict_types=1);

namespace Proovit\BotFilter\Traits;

use Illuminate\Http\Request;
use Proovit\BotFilter\BotFilter;
use Proovit\BotFilter\Models\BotProbe;
use Throwable;

trait InteractsWithBotFilter
{
    protected function botFilter(): BotFilter
    {
        return app(BotFilter::class);
    }

    protected function recordBotProbe(Request $request, Throwable $throwable): BotProbe
    {
        return $this->botFilter()->observe($request, $throwable);
    }
}
