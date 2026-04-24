<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Notification;
use Proovit\BotFilter\BotFilter;
use Proovit\BotFilter\Enums\BotProbeStatus;
use Proovit\BotFilter\Models\BotProbe;
use Proovit\BotFilter\Notifications\BotProbeDetectedNotification;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

it('records a probe once and notifies only on first sight', function (): void {
    Notification::fake();

    $botFilter = app(BotFilter::class);

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

    $first = $botFilter->record($context);
    $second = $botFilter->record($context);

    Notification::assertSentOnDemandTimes(BotProbeDetectedNotification::class, 1);

    expect($first->probe->count)->toBe(1);
    expect($second->probe->count)->toBe(2);

    $probe = BotProbe::query()->first();

    expect($probe)->not->toBeNull();
    expect($probe?->status)->toBe(BotProbeStatus::Notified);
    expect($probe?->notified_at)->not->toBeNull();
});
