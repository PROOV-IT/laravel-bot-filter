<?php

declare(strict_types=1);

namespace Proovit\UrlWatcher\Support;

use Illuminate\Support\Str;
use Proovit\UrlWatcher\Contracts\UrlWatcherSettingsRepositoryInterface;
use Proovit\UrlWatcher\Models\UrlWatcherSetting;

final class DefaultUrlWatcherSettingsRepository implements UrlWatcherSettingsRepositoryInterface
{
    private ?UrlWatcherSetting $cached = null;

    public function settings(): UrlWatcherSetting
    {
        if ($this->cached instanceof UrlWatcherSetting) {
            return $this->cached;
        }

        $this->cached = UrlWatcherSetting::query()->firstOrCreate(
            ['id' => 1],
            $this->defaults()
        );

        return $this->cached;
    }

    public function effectiveSettings(array $context = []): UrlWatcherSetting
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
            (array) config('url-watcher.rulesets', [])
        )));
    }

    public function update(array $attributes): UrlWatcherSetting
    {
        $settings = $this->settings();
        $settings->fill($attributes);
        $settings->save();
        $this->cached = $settings->refresh();

        return $this->cached;
    }

    public function reset(): UrlWatcherSetting
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
            'capture_enabled' => (bool) config('url-watcher.capture.enabled', true),
            'capture_exceptions' => (bool) config('url-watcher.capture.exceptions', true),
            'capture_statuses' => array_values((array) config('url-watcher.capture.statuses', [404, 405])),
            'ignore_paths' => array_values((array) config('url-watcher.capture.ignore.paths', [])),
            'ignore_hosts' => array_values((array) config('url-watcher.capture.ignore.hosts', [])),
            'ignore_panels' => array_values((array) config('url-watcher.capture.ignore.panels', [])),
            'ignore_methods' => array_values((array) config('url-watcher.capture.ignore.methods', [])),
            'ignore_exception_classes' => array_values((array) config('url-watcher.capture.ignore.exception_classes', [])),
            'notifications_enabled' => (bool) config('url-watcher.notification.enabled', true),
            'notification_mode' => (string) config('url-watcher.notification.mode', 'default'),
            'notification_title' => filled(config('url-watcher.notification.title')) ? (string) config('url-watcher.notification.title') : null,
            'notification_intro' => filled(config('url-watcher.notification.intro')) ? (string) config('url-watcher.notification.intro') : null,
            'notification_mail' => (string) config('url-watcher.notification.mail', config('app.admin_email', 'contact@proov-it.io')),
            'notification_route' => config('url-watcher.notification.route'),
            'custom_notification_class' => config('url-watcher.notification.custom_notification_class'),
            'active_ruleset' => null,
            'show_widgets' => (bool) config('url-watcher.show_widgets', true),
            'digest_enabled' => (bool) config('url-watcher.digest.enabled', false),
            'digest_mail' => (string) config('url-watcher.digest.mail', config('app.admin_email', 'contact@proov-it.io')),
            'digest_title' => filled(config('url-watcher.digest.title')) ? (string) config('url-watcher.digest.title') : null,
            'digest_intro' => filled(config('url-watcher.digest.intro')) ? (string) config('url-watcher.digest.intro') : null,
            'digest_window_hours' => (int) config('url-watcher.digest.window_hours', 24),
            'digest_notify_when_empty' => (bool) config('url-watcher.digest.notify_when_empty', false),
        ];
    }

    private function resolveRuleset(UrlWatcherSetting $settings, array $context = []): ?array
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

    private function applyRulesetOverrides(UrlWatcherSetting $settings, array $ruleset): UrlWatcherSetting
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
