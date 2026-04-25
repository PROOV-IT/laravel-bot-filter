<?php

declare(strict_types=1);

namespace Proovit\UrlWatcher\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Proovit\UrlWatcher\Models\UrlWatch;

final class UrlWatchDetectedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly UrlWatch $watch,
        public readonly ?string $title = null,
        public readonly ?string $intro = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $path = (string) ($this->watch->normalized_path ?? $this->watch->path);
        $introLines = array_values(array_filter(array_map(
            static fn (string $line): string => trim($line),
            preg_split('/\R+/', (string) $this->intro) ?: [],
        )));

        $message = (new MailMessage)
            ->subject($this->title ?: __('laravel-url-watcher::laravel-url-watcher.notifications.url_watch_detected.subject', ['path' => $path]));

        foreach ($introLines as $introLine) {
            $message->line($introLine);
        }

        return $message
            ->line(__('laravel-url-watcher::laravel-url-watcher.notifications.url_watch_detected.title'))
            ->line(__('laravel-url-watcher::laravel-url-watcher.notifications.url_watch_detected.path', ['path' => $this->watch->path]))
            ->line(__('laravel-url-watcher::laravel-url-watcher.notifications.url_watch_detected.host', ['host' => (string) $this->watch->host]))
            ->line(__('laravel-url-watcher::laravel-url-watcher.notifications.url_watch_detected.count', ['count' => (string) $this->watch->count]));
    }
}
