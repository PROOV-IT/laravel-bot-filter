<?php

declare(strict_types=1);

namespace Proovit\BotFilter\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Proovit\BotFilter\Models\BotProbe;

final class BotProbeDetectedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly BotProbe $probe,
        public readonly ?string $title = null,
        public readonly ?string $intro = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $path = (string) ($this->probe->normalized_path ?? $this->probe->path);
        $introLines = array_values(array_filter(array_map(
            static fn (string $line): string => trim($line),
            preg_split('/\R+/', (string) $this->intro) ?: [],
        )));

        $message = (new MailMessage)
            ->subject($this->title ?: __('laravel-bot-filter::laravel-bot-filter.notifications.bot_probe_detected.subject', ['path' => $path]));

        foreach ($introLines as $introLine) {
            $message->line($introLine);
        }

        return $message
            ->line(__('laravel-bot-filter::laravel-bot-filter.notifications.bot_probe_detected.title'))
            ->line(__('laravel-bot-filter::laravel-bot-filter.notifications.bot_probe_detected.path', ['path' => $this->probe->path]))
            ->line(__('laravel-bot-filter::laravel-bot-filter.notifications.bot_probe_detected.host', ['host' => (string) $this->probe->host]))
            ->line(__('laravel-bot-filter::laravel-bot-filter.notifications.bot_probe_detected.count', ['count' => (string) $this->probe->count]));
    }
}
