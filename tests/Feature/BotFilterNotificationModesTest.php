<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Notification;
use Proovit\BotFilter\BotFilter;
use Proovit\BotFilter\Contracts\BotFilterSettingsRepositoryInterface;
use Proovit\BotFilter\Models\BotProbe;
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
