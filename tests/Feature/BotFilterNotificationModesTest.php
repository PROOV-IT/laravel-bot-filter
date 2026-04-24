<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Notification;
use Proovit\BotFilter\BotFilter;
use Proovit\BotFilter\Contracts\BotFilterSettingsRepositoryInterface;
use Proovit\BotFilter\Models\BotProbe;
use Proovit\BotFilter\Notifications\BotProbeDetectedNotification;
use Tests\Support\TestCustomBotProbeNotification;

it('does not notify when notifications are disabled', function (): void {
    Notification::fake();

    app(BotFilterSettingsRepositoryInterface::class)->update([
        'notifications_enabled' => false,
    ]);

    app(BotFilter::class)->record([
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

    app(BotFilterSettingsRepositoryInterface::class)->update([
        'notifications_enabled' => true,
        'notification_mode' => 'custom',
        'notification_mail' => 'alerts@example.com',
        'custom_notification_class' => TestCustomBotProbeNotification::class,
    ]);

    app(BotFilter::class)->record([
        'method' => 'GET',
        'host' => 'api.proov-it.online',
        'path' => '/robots.txt',
        'normalized_path' => 'robots.txt',
        'panel' => 'admin',
        'meta' => [],
    ]);

    Notification::assertSentOnDemand(TestCustomBotProbeNotification::class, 1);

    expect(BotProbe::query()->count())->toBe(1);
});

it('uses custom title and intro on the package notification', function (): void {
    Notification::fake();

    app(BotFilterSettingsRepositoryInterface::class)->update([
        'notifications_enabled' => true,
        'notification_mode' => 'default',
        'notification_title' => 'Probe alert',
        'notification_intro' => "Hello team\nPlease review this incident.",
        'notification_mail' => 'alerts@example.com',
    ]);

    app(BotFilter::class)->record([
        'method' => 'GET',
        'host' => 'api.proov-it.online',
        'path' => '/phpinfo',
        'normalized_path' => 'phpinfo',
        'panel' => 'admin',
        'meta' => [],
    ]);

    Notification::assertSentOnDemand(BotProbeDetectedNotification::class, function (BotProbeDetectedNotification $notification, array $channels, mixed $notifiable): bool {
        $mail = $notification->toMail(new class {});

        return $notification->title === 'Probe alert'
            && $notification->intro === "Hello team\nPlease review this incident."
            && $mail->subject === 'Probe alert'
            && array_slice($mail->introLines, 0, 2) === ['Hello team', 'Please review this incident.'];
    });
});
