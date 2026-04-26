<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
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

it('can force a digest ruleset for anomaly-oriented delivery', function (): void {
    Notification::fake();

    Config::set('url-watcher.rulesets', [
        [
            'key' => 'ops-digest',
            'enabled' => true,
            'overrides' => [
                'digest_enabled' => true,
                'digest_mail' => 'digest-ruleset@example.com',
                'digest_recent_events_limit' => 1,
            ],
        ],
    ]);

    /** @var UrlWatcherSettingsRepositoryInterface $repository */
    $repository = app(UrlWatcherSettingsRepositoryInterface::class);
    $repository->update([
        'digest_enabled' => false,
    ]);

    $watch = UrlWatch::query()->create([
        'fingerprint' => str_repeat('d', 64),
        'path' => '/settings.ini',
        'normalized_path' => 'settings.ini',
        'host' => 'ops.example.com',
        'method' => 'POST',
        'count' => 3,
        'first_seen_at' => now()->subMinutes(30),
        'last_seen_at' => now()->subMinutes(2),
        'status' => 'pending',
        'classification' => 'bot',
    ]);

    UrlWatchEvent::query()->create([
        'url_watch_id' => $watch->getKey(),
        'method' => 'POST',
        'host' => 'ops.example.com',
        'path' => '/settings.ini',
        'normalized_path' => 'settings.ini',
        'status_code' => 404,
        'occurred_at' => now()->subMinutes(5),
    ]);

    Artisan::call('url-watcher:digest', ['--ruleset' => 'ops-digest']);

    Notification::assertSentOnDemand(UrlWatchDigestNotification::class, function ($notification, array $channels, object $notifiable): bool {
        return $notifiable->routes['mail'] === 'digest-ruleset@example.com'
            && ($notification->summary['write_attempts'] ?? null) === 1
            && array_key_exists('events_delta', $notification->summary)
            && array_key_exists('unique_hosts', $notification->summary);
    });
});

it('can force a retention ruleset', function (): void {
    Config::set('url-watcher.rulesets', [
        [
            'key' => 'aggressive-retention',
            'enabled' => true,
            'overrides' => [
                'retention_enabled' => true,
                'retention_days' => 7,
                'retention_prune_aggregates' => false,
            ],
        ],
    ]);

    /** @var UrlWatcherSettingsRepositoryInterface $repository */
    $repository = app(UrlWatcherSettingsRepositoryInterface::class);
    $repository->update([
        'retention_enabled' => false,
        'retention_days' => 30,
        'retention_prune_aggregates' => false,
    ]);

    $watch = UrlWatch::query()->create([
        'fingerprint' => str_repeat('e', 64),
        'path' => '/phpinfo',
        'normalized_path' => 'phpinfo',
        'host' => 'api.example.com',
        'method' => 'GET',
        'count' => 1,
        'first_seen_at' => now()->subDays(10),
        'last_seen_at' => now()->subDays(10),
        'status' => 'pending',
        'classification' => 'unknown',
    ]);

    UrlWatchEvent::query()->create([
        'url_watch_id' => $watch->getKey(),
        'method' => 'GET',
        'host' => 'api.example.com',
        'path' => '/phpinfo',
        'normalized_path' => 'phpinfo',
        'status_code' => 404,
        'occurred_at' => now()->subDays(8),
    ]);

    Artisan::call('url-watcher:prune', ['--ruleset' => 'aggressive-retention']);

    expect(UrlWatchEvent::query()->count())->toBe(0)
        ->and(UrlWatch::query()->whereKey($watch->getKey())->exists())->toBeTrue();
});
