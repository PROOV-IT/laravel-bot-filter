<?php

declare(strict_types=1);

namespace Proovit\BotFilter\Support;

use Illuminate\Support\Facades\Notification;
use Proovit\BotFilter\Contracts\BotProbeNotifierInterface;
use Proovit\BotFilter\Models\BotProbe;
use Proovit\BotFilter\Notifications\BotProbeDetectedNotification;

final class DefaultBotProbeNotifier implements BotProbeNotifierInterface
{
    public function notify(BotProbe $probe): void
    {
        $target = (string) config('bot-filter.notification.route')
            ?: (string) config('bot-filter.notification.mail', config('app.admin_email', 'contact@proov-it.io'));

        Notification::route('mail', $target)
            ->notify(new BotProbeDetectedNotification($probe));
    }
}
