<?php

declare(strict_types=1);

namespace Proovit\BotFilter\Support;

use Illuminate\Notifications\Notification as LaravelNotification;
use Illuminate\Support\Facades\Notification;
use Proovit\BotFilter\Contracts\BotFilterSettingsRepositoryInterface;
use Proovit\BotFilter\Contracts\BotProbeNotifierInterface;
use Proovit\BotFilter\Models\BotProbe;
use Proovit\BotFilter\Notifications\BotProbeDetectedNotification;

final class DefaultBotProbeNotifier implements BotProbeNotifierInterface
{
    public function __construct(private readonly BotFilterSettingsRepositoryInterface $settings) {}

    public function notify(BotProbe $probe): void
    {
        $settings = $this->settings->effectiveSettings([
            'environment' => app()->environment(),
            'host' => (string) ($probe->host ?? ''),
            'panel' => (string) ($probe->panel ?? ''),
        ]);

        if (! (bool) $settings->notifications_enabled) {
            return;
        }

        $target = (string) $settings->notification_route
            ?: (string) $settings->notification_mail
            ?: (string) config('app.admin_email', 'contact@proov-it.io');

        $notification = $this->resolveNotification(
            $probe,
            (string) $settings->notification_mode,
            $settings->custom_notification_class,
            filled($settings->notification_title ?? null) ? (string) $settings->notification_title : null,
            filled($settings->notification_intro ?? null) ? (string) $settings->notification_intro : null,
        );

        Notification::route('mail', $target)
            ->notify($notification);
    }

    private function resolveNotification(BotProbe $probe, string $mode, ?string $customClass, ?string $title, ?string $intro): LaravelNotification
    {
        if ($mode === 'custom' && is_string($customClass) && class_exists($customClass)) {
            try {
                $notification = app($customClass, ['probe' => $probe]);

                if ($notification instanceof LaravelNotification) {
                    return $notification;
                }
            } catch (\Throwable) {
                //
            }
        }

        return new BotProbeDetectedNotification($probe, $title, $intro);
    }
}
