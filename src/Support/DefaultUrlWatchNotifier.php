<?php

declare(strict_types=1);

namespace Proovit\UrlWatcher\Support;

use Illuminate\Notifications\Notification as LaravelNotification;
use Illuminate\Support\Facades\Notification;
use Proovit\UrlWatcher\Contracts\UrlWatcherSettingsRepositoryInterface;
use Proovit\UrlWatcher\Contracts\UrlWatchNotifierInterface;
use Proovit\UrlWatcher\Models\UrlWatch;
use Proovit\UrlWatcher\Notifications\UrlWatchDetectedNotification;

final class DefaultUrlWatchNotifier implements UrlWatchNotifierInterface
{
    public function __construct(private readonly UrlWatcherSettingsRepositoryInterface $settings) {}

    public function notify(UrlWatch $watch): void
    {
        $settings = $this->settings->effectiveSettings([
            'environment' => app()->environment(),
            'host' => (string) ($watch->host ?? ''),
            'panel' => (string) ($watch->panel ?? ''),
        ]);

        if (! (bool) $settings->notifications_enabled) {
            return;
        }

        $target = (string) $settings->notification_route
            ?: (string) $settings->notification_mail
            ?: (string) config('app.admin_email', 'contact@proov-it.io');

        $notification = $this->resolveNotification(
            $watch,
            (string) $settings->notification_mode,
            $settings->custom_notification_class,
            filled($settings->notification_title ?? null) ? (string) $settings->notification_title : null,
            filled($settings->notification_intro ?? null) ? (string) $settings->notification_intro : null,
        );

        Notification::route('mail', $target)
            ->notify($notification);
    }

    private function resolveNotification(UrlWatch $watch, string $mode, ?string $customClass, ?string $title, ?string $intro): LaravelNotification
    {
        if ($mode === 'custom' && is_string($customClass) && class_exists($customClass)) {
            try {
                $notification = new $customClass($watch);

                if ($notification instanceof LaravelNotification) {
                    return $notification;
                }
            } catch (\Throwable) {
                try {
                    $notification = app($customClass, [
                        'watch' => $watch,
                        'urlWatch' => $watch,
                        'probe' => $watch,
                    ]);

                    if ($notification instanceof LaravelNotification) {
                        return $notification;
                    }
                } catch (\Throwable) {
                    //
                }
            }
        }

        return new UrlWatchDetectedNotification($watch, $title, $intro);
    }
}
