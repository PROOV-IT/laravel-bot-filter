<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Notification;
use Proovit\UrlWatcher\Contracts\UrlWatcherSettingsRepositoryInterface;
use Proovit\UrlWatcher\Models\UrlWatch;
use Proovit\UrlWatcher\Notifications\UrlWatchDetectedNotification;
use Proovit\UrlWatcher\UrlWatcher;
use Tests\Support\TestCustomUrlWatchNotification;

it('does not notify when notifications are disabled', function (): void {
    Notification::fake();

    app(UrlWatcherSettingsRepositoryInterface::class)->update([
        'notifications_enabled' => false,
    ]);

    app(UrlWatcher::class)->record([
        'method' => 'GET',
        'host' => 'api.proov-it.online',
        'path' => '/robots.txt',
        'normalized_path' => 'robots.txt',
        'panel' => 'admin',
        'meta' => [],
    ]);

    Notification::assertNothingSent();
});

it('uses a custom notification when configured', function (): void {
    Notification::fake();

    app(UrlWatcherSettingsRepositoryInterface::class)->update([
        'notifications_enabled' => true,
        'notification_mode' => 'custom',
        'notification_mail' => 'alerts@example.com',
        'custom_notification_class' => TestCustomUrlWatchNotification::class,
    ]);

    app(UrlWatcher::class)->record([
        'method' => 'GET',
        'host' => 'api.proov-it.online',
        'path' => '/robots.txt',
        'normalized_path' => 'robots.txt',
        'panel' => 'admin',
        'meta' => [],
    ]);

    Notification::assertSentOnDemand(TestCustomUrlWatchNotification::class, 1);

    expect(UrlWatch::query()->count())->toBe(1);
});

it('uses custom title and intro on the package notification', function (): void {
    Notification::fake();

    app(UrlWatcherSettingsRepositoryInterface::class)->update([
        'notifications_enabled' => true,
        'notification_mode' => 'default',
        'notification_title' => 'URL watch alert',
        'notification_intro' => "Hello team\nPlease review this incident.",
        'notification_mail' => 'alerts@example.com',
    ]);

    app(UrlWatcher::class)->record([
        'method' => 'GET',
        'host' => 'api.proov-it.online',
        'path' => '/phpinfo',
        'normalized_path' => 'phpinfo',
        'panel' => 'admin',
        'meta' => [],
    ]);

    Notification::assertSentOnDemand(UrlWatchDetectedNotification::class, function (UrlWatchDetectedNotification $notification, array $channels, mixed $notifiable): bool {
        $mail = $notification->toMail(new class {});

        return $notification->title === 'URL watch alert'
            && $notification->intro === "Hello team\nPlease review this incident."
            && $mail->subject === 'URL watch alert'
            && array_slice($mail->introLines, 0, 2) === ['Hello team', 'Please review this incident.'];
    });
});
