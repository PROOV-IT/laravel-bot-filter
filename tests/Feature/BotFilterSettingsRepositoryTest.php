<?php

declare(strict_types=1);

use Proovit\BotFilter\Contracts\BotFilterSettingsRepositoryInterface;

it('resets bot filter settings to defaults', function (): void {
    $repository = app(BotFilterSettingsRepositoryInterface::class);

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
        ->and($settings->show_widgets)->toBeTrue();
});
