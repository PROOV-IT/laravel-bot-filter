<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Notification;
use Proovit\UrlWatcher\Enums\UrlWatchStatus;
use Proovit\UrlWatcher\Models\UrlWatch;
use Proovit\UrlWatcher\Notifications\UrlWatchDetectedNotification;
use Proovit\UrlWatcher\UrlWatcher;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

it('records a watch once, stores history, and notifies only on first sight', function (): void {
    Notification::fake();

    $watcher = app(UrlWatcher::class);

    $context = [
        'exception_class' => RouteNotFoundException::class,
        'method' => 'GET',
        'host' => 'api.proov-it.online',
        'path' => '/robots.txt',
        'normalized_path' => 'robots.txt',
        'route_name' => null,
        'ip' => '127.0.0.1',
        'user_agent' => 'Bot',
        'panel' => 'admin',
        'meta' => ['source' => 'test'],
    ];

    $first = $watcher->record($context);
    $second = $watcher->record($context);

    Notification::assertSentOnDemandTimes(UrlWatchDetectedNotification::class, 1);

    expect($first->watch->count)->toBe(1);
    expect($second->watch->count)->toBe(2);
    expect($first->event->path)->toBe('/robots.txt');
    expect($second->event->normalized_path)->toBe('robots.txt');

    $watch = UrlWatch::query()->first();

    expect($watch)->not->toBeNull();
    expect($watch?->status)->toBe(UrlWatchStatus::Notified);
    expect($watch?->notified_at)->not->toBeNull();
    expect($watch?->events()->count())->toBe(2);
});
