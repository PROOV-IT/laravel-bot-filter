<?php

declare(strict_types=1);

namespace Proovit\UrlWatcher\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class UrlWatchDigestNotification extends Notification
{
    use Queueable;

    /**
     * @param  array<string, mixed>  $summary
     */
    public function __construct(
        public readonly array $summary,
        public readonly ?string $title = null,
        public readonly ?string $intro = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject($this->title ?: (string) ($this->summary['subject'] ?? __('laravel-url-watcher::laravel-url-watcher.notifications.url_watch_digest.subject')));

        foreach ($this->introLines() as $line) {
            $message->line($line);
        }

        $message
            ->line(__('laravel-url-watcher::laravel-url-watcher.notifications.url_watch_digest.period', ['hours' => (string) ($this->summary['window_hours'] ?? 24)]))
            ->line(__('laravel-url-watcher::laravel-url-watcher.notifications.url_watch_digest.total', ['count' => (string) ($this->summary['total'] ?? 0)]))
            ->line(__('laravel-url-watcher::laravel-url-watcher.notifications.url_watch_digest.events_total', ['count' => (string) ($this->summary['events_total'] ?? 0)]))
            ->line(__('laravel-url-watcher::laravel-url-watcher.notifications.url_watch_digest.pending', ['count' => (string) ($this->summary['pending'] ?? 0)]))
            ->line(__('laravel-url-watcher::laravel-url-watcher.notifications.url_watch_digest.reviewed', ['count' => (string) ($this->summary['reviewed'] ?? 0)]))
            ->line(__('laravel-url-watcher::laravel-url-watcher.notifications.url_watch_digest.bots', ['count' => (string) ($this->summary['bots'] ?? 0)]))
            ->line(__('laravel-url-watcher::laravel-url-watcher.notifications.url_watch_digest.normal', ['count' => (string) ($this->summary['normal'] ?? 0)]))
            ->line(__('laravel-url-watcher::laravel-url-watcher.notifications.url_watch_digest.ignored', ['count' => (string) ($this->summary['ignored'] ?? 0)]));

        $this->addRows(
            $message,
            __('laravel-url-watcher::laravel-url-watcher.notifications.url_watch_digest.top_paths_heading'),
            (array) ($this->summary['top_paths'] ?? []),
        );

        $this->addRows(
            $message,
            __('laravel-url-watcher::laravel-url-watcher.notifications.url_watch_digest.top_hosts_heading'),
            (array) ($this->summary['top_hosts'] ?? []),
        );

        $this->addRows(
            $message,
            __('laravel-url-watcher::laravel-url-watcher.notifications.url_watch_digest.top_methods_heading'),
            (array) ($this->summary['top_methods'] ?? []),
        );

        $this->addRows(
            $message,
            __('laravel-url-watcher::laravel-url-watcher.notifications.url_watch_digest.top_statuses_heading'),
            (array) ($this->summary['top_statuses'] ?? []),
        );

        $recentEvents = (array) ($this->summary['recent_events'] ?? []);

        if ($recentEvents !== []) {
            $message->line(__('laravel-url-watcher::laravel-url-watcher.notifications.url_watch_digest.recent_events_heading'));

            foreach ($recentEvents as $event) {
                $message->line(__('laravel-url-watcher::laravel-url-watcher.notifications.url_watch_digest.recent_event', [
                    'occurred_at' => (string) ($event['occurred_at'] ?? '-'),
                    'method' => (string) ($event['method'] ?? '-'),
                    'host' => (string) ($event['host'] ?? '-'),
                    'path' => (string) ($event['path'] ?? '/'),
                    'status' => (string) ($event['status_code'] ?? '-'),
                ]));
            }
        }

        return $message;
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    private function addRows(MailMessage $message, string $heading, array $rows): void
    {
        if ($rows === []) {
            return;
        }

        $message->line($heading);

        foreach ($rows as $row) {
            $message->line(__('laravel-url-watcher::laravel-url-watcher.notifications.url_watch_digest.row', [
                'label' => (string) ($row['label'] ?? '-'),
                'count' => (string) ($row['count'] ?? 0),
            ]));
        }
    }

    /**
     * @return array<int, string>
     */
    private function introLines(): array
    {
        return array_values(array_filter(array_map(
            static fn (string $line): string => trim($line),
            preg_split('/\R+/', (string) $this->intro) ?: [],
        )));
    }
}
