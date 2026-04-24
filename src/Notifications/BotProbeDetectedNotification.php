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

    public function __construct(public readonly BotProbe $probe) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $path = (string) ($this->probe->normalized_path ?? $this->probe->path);

        return (new MailMessage)
            ->subject(__('laravel-bot-filter::laravel-bot-filter.notifications.bot_probe_detected.subject', ['path' => $path]))
            ->line(__('laravel-bot-filter::laravel-bot-filter.notifications.bot_probe_detected.title'))
            ->line(__('laravel-bot-filter::laravel-bot-filter.notifications.bot_probe_detected.path', ['path' => $this->probe->path]))
            ->line(__('laravel-bot-filter::laravel-bot-filter.notifications.bot_probe_detected.host', ['host' => (string) $this->probe->host]))
            ->line(__('laravel-bot-filter::laravel-bot-filter.notifications.bot_probe_detected.count', ['count' => (string) $this->probe->count]));
    }
}
