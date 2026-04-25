<?php

declare(strict_types=1);

namespace Proovit\BotFilter\Support;

use Illuminate\Support\Str;
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

    public function effectiveSettings(array $context = []): BotFilterSetting
    {
        $settings = $this->settings();
        $ruleset = $this->resolveRuleset($settings, $context);

        if ($ruleset === null) {
            return $settings;
        }

        $effective = $settings->replicate();
        $effective->exists = true;

        return $this->applyRulesetOverrides($effective, $ruleset);
    }

    public function rulesets(): array
    {
        return array_values(array_filter(array_map(
            static fn (mixed $ruleset): array => is_array($ruleset) ? $ruleset : [],
            (array) config('bot-filter.rulesets', [])
        )));
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
            'notification_title' => filled(config('bot-filter.notification.title')) ? (string) config('bot-filter.notification.title') : null,
            'notification_intro' => filled(config('bot-filter.notification.intro')) ? (string) config('bot-filter.notification.intro') : null,
            'notification_mail' => (string) config('bot-filter.notification.mail', config('app.admin_email', 'contact@proov-it.io')),
            'notification_route' => config('bot-filter.notification.route'),
            'custom_notification_class' => config('bot-filter.notification.custom_notification_class'),
            'active_ruleset' => null,
            'show_widgets' => (bool) config('bot-filter.show_widgets', true),
            'digest_enabled' => (bool) config('bot-filter.digest.enabled', false),
            'digest_mail' => (string) config('bot-filter.digest.mail', config('app.admin_email', 'contact@proov-it.io')),
            'digest_title' => filled(config('bot-filter.digest.title')) ? (string) config('bot-filter.digest.title') : null,
            'digest_intro' => filled(config('bot-filter.digest.intro')) ? (string) config('bot-filter.digest.intro') : null,
            'digest_window_hours' => (int) config('bot-filter.digest.window_hours', 24),
            'digest_notify_when_empty' => (bool) config('bot-filter.digest.notify_when_empty', false),
        ];
    }

    private function resolveRuleset(BotFilterSetting $settings, array $context = []): ?array
    {
        $rulesets = $this->rulesets();

        if ($rulesets === []) {
            return null;
        }

        $activeRuleset = trim((string) ($settings->active_ruleset ?? ''));

        if ($activeRuleset !== '') {
            foreach ($rulesets as $ruleset) {
                if (($ruleset['key'] ?? null) === $activeRuleset && (bool) ($ruleset['enabled'] ?? true)) {
                    return $ruleset;
                }
            }
        }

        $environment = strtolower((string) ($context['environment'] ?? app()->environment()));
        $host = strtolower(trim((string) ($context['host'] ?? '')));
        $panel = strtolower(trim((string) ($context['panel'] ?? '')));

        foreach ($rulesets as $ruleset) {
            if (! (bool) ($ruleset['enabled'] ?? true)) {
                continue;
            }

            $environments = array_map(
                static fn (mixed $value): string => strtolower(trim((string) $value)),
                (array) ($ruleset['environments'] ?? []),
            );
            $hosts = array_map(
                static fn (mixed $value): string => strtolower(trim((string) $value)),
                (array) ($ruleset['hosts'] ?? []),
            );
            $panels = array_map(
                static fn (mixed $value): string => strtolower(trim((string) $value)),
                (array) ($ruleset['panels'] ?? []),
            );

            if ($environments !== [] && ! in_array($environment, $environments, true)) {
                continue;
            }

            if ($hosts !== [] && ! $this->matchesAny($hosts, $host)) {
                continue;
            }

            if ($panels !== [] && ! $this->matchesAny($panels, $panel)) {
                continue;
            }

            return $ruleset;
        }

        return null;
    }

    private function applyRulesetOverrides(BotFilterSetting $settings, array $ruleset): BotFilterSetting
    {
        $overrides = (array) ($ruleset['overrides'] ?? []);

        foreach ([
            'capture_enabled',
            'capture_exceptions',
            'notifications_enabled',
            'notification_mode',
            'notification_title',
            'notification_intro',
            'notification_mail',
            'notification_route',
            'custom_notification_class',
            'active_ruleset',
            'show_widgets',
            'digest_enabled',
            'digest_mail',
            'digest_title',
            'digest_intro',
            'digest_window_hours',
            'digest_notify_when_empty',
        ] as $attribute) {
            if (! array_key_exists($attribute, $overrides)) {
                continue;
            }

            $settings->{$attribute} = $overrides[$attribute];
        }

        foreach ([
            'capture_statuses',
            'ignore_paths',
            'ignore_hosts',
            'ignore_panels',
            'ignore_methods',
            'ignore_exception_classes',
        ] as $attribute) {
            if (! array_key_exists($attribute, $overrides)) {
                continue;
            }

            $settings->{$attribute} = array_values((array) $overrides[$attribute]);
        }

        return $settings;
    }

    private function matchesAny(array $patterns, string $value): bool
    {
        $value = strtolower(trim($value));

        foreach ($patterns as $pattern) {
            $pattern = strtolower(trim((string) $pattern));

            if ($pattern === '') {
                continue;
            }

            if (Str::is($pattern, $value)) {
                return true;
            }
        }

        return false;
    }
}
