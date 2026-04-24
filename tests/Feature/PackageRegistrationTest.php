<?php

declare(strict_types=1);

use Proovit\BotFilter\BotFilter;
use Proovit\BotFilter\BotFilterServiceProvider;
use Proovit\BotFilter\Support\BotProbeCatalog;

it('registers the bot filter package', function (): void {
    expect(app(BotFilter::class))->toBeInstanceOf(BotFilter::class);
    expect(app(BotProbeCatalog::class))->toBeInstanceOf(BotProbeCatalog::class);
    expect(BotFilterServiceProvider::class)->toBeString();
});
