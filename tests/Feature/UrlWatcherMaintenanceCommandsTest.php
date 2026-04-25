<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Notification;
use Proovit\UrlWatcher\Contracts\UrlWatcherSettingsRepositoryInterface;
use Proovit\UrlWatcher\Models\UrlWatch;
use Proovit\UrlWatcher\Models\UrlWatchEvent;
use Proovit\UrlWatcher\Notifications\UrlWatchDigestNotification;

it('sends an enriched digest with top hosts and recent events', function (): void {
    Notification::fake();

    /** @var UrlWatcherSettingsRepositoryInterface $repository */
    $repository = app(UrlWatcherSettingsRepositoryInterface::class);
    $repository->update([
        'digest_enabled' => true,
        'digest_mail' => 'ops@example.com',
        'digest_recent_events_limit' => 2,
    ]);

    $watch = UrlWatch::query()->create([
        'fingerprint' => str_repeat('a', 64),
        'path' => '/wp-login.php',
        'normalized_path' => 'wp-login.php',
        'host' => 'api.example.com',
        'method' => 'GET',
        'count' => 2,
        'first_seen_at' => now()->subHour(),
        'last_seen_at' => now()->subMinutes(5),
        'status' => 'pending',
        'classification' => 'bot',
    ]);

    UrlWatchEvent::query()->create([
        'url_watch_id' => $watch->getKey(),
        'method' => 'GET',
        'host' => 'api.example.com',
        'path' => '/wp-login.php',
        'normalized_path' => 'wp-login.php',
        'status_code' => 404,
        'occurred_at' => now()->subMinutes(4),
    ]);

    UrlWatchEvent::query()->create([
        'url_watch_id' => $watch->getKey(),
        'method' => 'POST',
        'host' => 'api.example.com',
        'path' => '/wp-login.php',
        'normalized_path' => 'wp-login.php',
        'status_code' => 405,
        'occurred_at' => now()->subMinutes(2),
    ]);

    Artisan::call('url-watcher:digest');

    Notification::assertSentOnDemandTimes(UrlWatchDigestNotification::class, 1);
});

it('prunes old history events and optional aggregates', function (): void {
    /** @var UrlWatcherSettingsRepositoryInterface $repository */
    $repository = app(UrlWatcherSettingsRepositoryInterface::class);
    $repository->update([
        'retention_enabled' => true,
        'retention_days' => 30,
        'retention_prune_aggregates' => true,
    ]);

    $staleWatch = UrlWatch::query()->create([
        'fingerprint' => str_repeat('b', 64),
        'path' => '/robots.txt',
        'normalized_path' => 'robots.txt',
        'host' => 'api.example.com',
        'method' => 'GET',
        'count' => 1,
        'first_seen_at' => now()->subDays(60),
        'last_seen_at' => now()->subDays(60),
        'status' => 'archived',
        'classification' => 'ignored',
    ]);

    $freshWatch = UrlWatch::query()->create([
        'fingerprint' => str_repeat('c', 64),
        'path' => '/phpinfo',
        'normalized_path' => 'phpinfo',
        'host' => 'api.example.com',
        'method' => 'GET',
        'count' => 1,
        'first_seen_at' => now()->subDays(2),
        'last_seen_at' => now()->subDays(2),
        'status' => 'pending',
        'classification' => 'unknown',
    ]);

    UrlWatchEvent::query()->create([
        'url_watch_id' => $staleWatch->getKey(),
        'method' => 'GET',
        'host' => 'api.example.com',
        'path' => '/robots.txt',
        'normalized_path' => 'robots.txt',
        'status_code' => 404,
        'occurred_at' => now()->subDays(45),
    ]);

    UrlWatchEvent::query()->create([
        'url_watch_id' => $freshWatch->getKey(),
        'method' => 'GET',
        'host' => 'api.example.com',
        'path' => '/phpinfo',
        'normalized_path' => 'phpinfo',
        'status_code' => 404,
        'occurred_at' => now()->subDays(1),
    ]);

    Artisan::call('url-watcher:prune');

    expect(UrlWatchEvent::query()->count())->toBe(1)
        ->and(UrlWatch::query()->whereKey($staleWatch->getKey())->exists())->toBeFalse()
        ->and(UrlWatch::query()->whereKey($freshWatch->getKey())->exists())->toBeTrue();
});
