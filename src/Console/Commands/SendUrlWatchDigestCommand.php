<?php

declare(strict_types=1);

namespace Proovit\UrlWatcher\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;
use Proovit\UrlWatcher\Contracts\UrlWatcherSettingsRepositoryInterface;
use Proovit\UrlWatcher\Enums\UrlWatchClassification;
use Proovit\UrlWatcher\Enums\UrlWatchStatus;
use Proovit\UrlWatcher\Models\UrlWatch;
use Proovit\UrlWatcher\Notifications\UrlWatchDigestNotification;

final class SendUrlWatchDigestCommand extends Command
{
    protected $signature = 'url-watcher:digest {--hours= : Override the digest window in hours} {--force : Send the digest even when no watch was found}';

    protected $description = 'Send a URL watcher digest notification.';

    public function handle(UrlWatcherSettingsRepositoryInterface $settingsRepository): int
    {
        $settings = $settingsRepository->effectiveSettings();

        if (! (bool) $settings->digest_enabled) {
            $this->info('URL watcher digest notifications are disabled.');

            return self::SUCCESS;
        }

        $windowHours = max(1, (int) ($this->option('hours') ?: $settings->digest_window_hours ?: 24));
        $since = now()->subHours($windowHours);

        $query = UrlWatch::query()->where('last_seen_at', '>=', $since);

        $summary = [
            'window_hours' => $windowHours,
            'total' => (clone $query)->count(),
            'pending' => (clone $query)->where('status', UrlWatchStatus::Pending->value)->count(),
            'reviewed' => (clone $query)->where('status', UrlWatchStatus::Reviewed->value)->count(),
            'archived' => (clone $query)->where('status', UrlWatchStatus::Archived->value)->count(),
            'bots' => (clone $query)->where('classification', UrlWatchClassification::Bot->value)->count(),
            'normal' => (clone $query)->where('classification', UrlWatchClassification::Normal->value)->count(),
            'ignored' => (clone $query)->where('classification', UrlWatchClassification::Ignored->value)->count(),
            'top_paths' => (clone $query)
                ->selectRaw('COALESCE(NULLIF(normalized_path, \'\'), path) as label, COUNT(*) as count')
                ->groupByRaw('COALESCE(NULLIF(normalized_path, \'\'), path)')
                ->orderByDesc('count')
                ->limit(10)
                ->get()
                ->map(static fn (UrlWatch $watch): array => [
                    'label' => (string) $watch->getAttribute('label'),
                    'count' => (int) $watch->getAttribute('count'),
                ])
                ->all(),
            'top_hosts' => (clone $query)
                ->selectRaw('COALESCE(NULLIF(host, \'\'), \'-\') as label, COUNT(*) as count')
                ->groupByRaw('COALESCE(NULLIF(host, \'\'), \'-\')')
                ->orderByDesc('count')
                ->limit(10)
                ->get()
                ->map(static fn (UrlWatch $watch): array => [
                    'label' => (string) $watch->getAttribute('label'),
                    'count' => (int) $watch->getAttribute('count'),
                ])
                ->all(),
        ];

        if ($summary['total'] === 0 && ! (bool) $settings->digest_notify_when_empty) {
            $this->info('No URL watches found in the selected digest window.');

            return self::SUCCESS;
        }

        $target = (string) ($settings->digest_mail ?: config('app.admin_email', 'contact@proov-it.io'));

        Notification::route('mail', $target)
            ->notify(new UrlWatchDigestNotification(
                $summary,
                filled($settings->digest_title ?? null) ? (string) $settings->digest_title : null,
                filled($settings->digest_intro ?? null) ? (string) $settings->digest_intro : null,
            ));

        $this->info(sprintf(
            'Sent URL watcher digest to %s for the last %d hour(s).',
            $target,
            $windowHours,
        ));

        return self::SUCCESS;
    }
}
