<?php

declare(strict_types=1);

namespace Proovit\BotFilter\Support;

use Proovit\BotFilter\Contracts\BotFilterSettingsRepositoryInterface;
use Proovit\BotFilter\Models\BotFilterSetting;

final class DefaultBotFilterSettingsRepository implements BotFilterSettingsRepositoryInterface
{
    private ?BotFilterSetting $cached = null;

    public function settings(): BotFilterSetting
    {
        if ($this->cached instanceof BotFilterSetting) {
            return $this->cached;
        }

        $this->cached = BotFilterSetting::query()->firstOrCreate(
            ['id' => 1],
            $this->defaults()
        );

        return $this->cached;
    }

    public function update(array $attributes): BotFilterSetting
    {
        $settings = $this->settings();
        $settings->fill($attributes);
        $settings->save();
        $this->cached = $settings->refresh();

        return $this->cached;
    }

    public function reset(): BotFilterSetting
    {
        $settings = $this->settings();
        $settings->fill($this->defaults());
        $settings->save();
        $this->cached = $settings->refresh();

        return $this->cached;
    }

    /**
     * @return array<string, mixed>
     */
    private function defaults(): array
    {
        return [
            'capture_enabled' => (bool) config('bot-filter.capture.enabled', true),
            'capture_exceptions' => (bool) config('bot-filter.capture.exceptions', true),
            'capture_statuses' => array_values((array) config('bot-filter.capture.statuses', [404, 405])),
            'ignore_paths' => array_values((array) config('bot-filter.capture.ignore.paths', [])),
            'ignore_hosts' => array_values((array) config('bot-filter.capture.ignore.hosts', [])),
            'ignore_panels' => array_values((array) config('bot-filter.capture.ignore.panels', [])),
            'ignore_methods' => array_values((array) config('bot-filter.capture.ignore.methods', [])),
            'ignore_exception_classes' => array_values((array) config('bot-filter.capture.ignore.exception_classes', [])),
            'notifications_enabled' => (bool) config('bot-filter.notification.enabled', true),
            'notification_mode' => (string) config('bot-filter.notification.mode', 'default'),
            'notification_mail' => (string) config('bot-filter.notification.mail', config('app.admin_email', 'contact@proov-it.io')),
            'notification_route' => config('bot-filter.notification.route'),
            'custom_notification_class' => config('bot-filter.notification.custom_notification_class'),
            'show_widgets' => (bool) config('bot-filter.show_widgets', true),
        ];
    }
}
