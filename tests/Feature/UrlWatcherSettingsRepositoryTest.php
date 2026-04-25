<?php

declare(strict_types=1);

use Proovit\UrlWatcher\Contracts\UrlWatcherSettingsRepositoryInterface;

it('resets URL watcher settings to defaults', function (): void {
    $repository = app(UrlWatcherSettingsRepositoryInterface::class);

    $repository->update([
        'capture_enabled' => false,
        'capture_exceptions' => false,
        'capture_statuses' => [401],
        'ignore_paths' => ['foo'],
        'ignore_hosts' => ['bar.example'],
        'ignore_panels' => ['admin'],
        'ignore_methods' => ['POST'],
        'ignore_exception_classes' => [RuntimeException::class],
        'notifications_enabled' => false,
        'notification_mode' => 'custom',
        'notification_title' => 'Custom title',
        'notification_intro' => "Line 1\nLine 2",
        'notification_mail' => 'ops@example.com',
        'notification_route' => 'route@example.com',
        'custom_notification_class' => 'App\\Notifications\\Custom',
        'show_widgets' => false,
        'digest_recent_events_limit' => 5,
        'retention_enabled' => true,
        'retention_days' => 14,
        'retention_prune_aggregates' => true,
    ]);

    $settings = $repository->reset();

    expect($settings->capture_enabled)->toBeTrue()
        ->and($settings->capture_exceptions)->toBeTrue()
        ->and($settings->capture_statuses)->toBe([404, 405])
        ->and($settings->ignore_paths)->toBe([])
        ->and($settings->ignore_hosts)->toBe([])
        ->and($settings->ignore_panels)->toBe([])
        ->and($settings->ignore_methods)->toBe([])
        ->and($settings->ignore_exception_classes)->toBe([])
        ->and($settings->notifications_enabled)->toBeTrue()
        ->and($settings->notification_mode)->toBe('default')
        ->and($settings->notification_title)->toBeNull()
        ->and($settings->notification_intro)->toBeNull()
        ->and($settings->notification_mail)->toBe('contact@proov-it.io')
        ->and($settings->custom_notification_class)->toBeNull()
        ->and($settings->show_widgets)->toBeTrue()
        ->and($settings->digest_recent_events_limit)->toBe(10)
        ->and($settings->retention_enabled)->toBeFalse()
        ->and($settings->retention_days)->toBe(30)
        ->and($settings->retention_prune_aggregates)->toBeFalse();
});

it('applies the matching active ruleset overrides', function (): void {
    config()->set('url-watcher.rulesets', [
        [
            'key' => 'production',
            'label' => 'Production',
            'enabled' => true,
            'environments' => ['production'],
            'hosts' => ['*.example.com'],
            'panels' => ['admin'],
            'overrides' => [
                'notifications_enabled' => false,
                'notification_title' => 'Production URL watch',
                'digest_enabled' => true,
                'digest_window_hours' => 12,
                'digest_recent_events_limit' => 4,
                'retention_enabled' => true,
                'retention_days' => 21,
                'capture_statuses' => [403],
            ],
        ],
    ]);

    $repository = app(UrlWatcherSettingsRepositoryInterface::class);
    $repository->update([
        'active_ruleset' => 'production',
        'notifications_enabled' => true,
        'notification_title' => null,
        'digest_enabled' => false,
        'digest_window_hours' => 24,
        'capture_statuses' => [404, 405],
    ]);

    $effective = $repository->effectiveSettings([
        'environment' => 'local',
        'host' => 'api.example.com',
        'panel' => 'admin',
    ]);

    expect($effective->notifications_enabled)->toBeFalse()
        ->and($effective->notification_title)->toBe('Production URL watch')
        ->and($effective->digest_enabled)->toBeTrue()
        ->and($effective->digest_window_hours)->toBe(12)
        ->and($effective->digest_recent_events_limit)->toBe(4)
        ->and($effective->retention_enabled)->toBeTrue()
        ->and($effective->retention_days)->toBe(21)
        ->and($effective->capture_statuses)->toBe([403]);
});

it('auto-matches a ruleset by context when no explicit ruleset is selected', function (): void {
    config()->set('url-watcher.rulesets', [
        [
            'key' => 'staging',
            'label' => 'Staging',
            'enabled' => true,
            'environments' => ['testing'],
            'hosts' => ['*.staging.example.com'],
            'panels' => ['b2b'],
            'overrides' => [
                'notification_title' => 'Staging URL watch',
                'digest_notify_when_empty' => true,
            ],
        ],
    ]);

    $repository = app(UrlWatcherSettingsRepositoryInterface::class);
    $repository->reset();

    $effective = $repository->effectiveSettings([
        'environment' => 'testing',
        'host' => 'api.staging.example.com',
        'panel' => 'b2b',
    ]);

    expect($effective->notification_title)->toBe('Staging URL watch')
        ->and($effective->digest_notify_when_empty)->toBeTrue();
});
