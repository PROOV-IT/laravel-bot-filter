<?php

declare(strict_types=1);

namespace Tests\Support;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Proovit\UrlWatcher\Models\UrlWatch;

final class TestCustomUrlWatchNotification extends Notification
{
    public function __construct(public readonly UrlWatch $watch) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Custom URL watch notification')
            ->line((string) $this->watch->normalized_path);
    }
}
