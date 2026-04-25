<?php

declare(strict_types=1);

use Proovit\UrlWatcher\Support\UrlWatchCatalog;
use Proovit\UrlWatcher\UrlWatcher;
use Proovit\UrlWatcher\UrlWatcherServiceProvider;

it('registers the URL watcher package', function (): void {
    expect(app(UrlWatcher::class))->toBeInstanceOf(UrlWatcher::class);
    expect(app(UrlWatchCatalog::class))->toBeInstanceOf(UrlWatchCatalog::class);
    expect(UrlWatcherServiceProvider::class)->toBeString();
});
