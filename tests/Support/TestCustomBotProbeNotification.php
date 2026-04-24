<?php

declare(strict_types=1);

namespace Tests\Support;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Proovit\BotFilter\Models\BotProbe;

final class TestCustomBotProbeNotification extends Notification
{
    public function __construct(public readonly BotProbe $probe) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Custom bot probe notification')
            ->line((string) $this->probe->normalized_path);
    }
}
