<?php

declare(strict_types=1);

namespace Proovit\UrlWatcher\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;
use Proovit\UrlWatcher\Contracts\UrlWatcherSettingsRepositoryInterface;
use Proovit\UrlWatcher\Enums\UrlWatchClassification;
use Proovit\UrlWatcher\Enums\UrlWatchStatus;
use Proovit\UrlWatcher\Models\UrlWatch;
use Proovit\UrlWatcher\Models\UrlWatchEvent;
use Proovit\UrlWatcher\Notifications\UrlWatchDigestNotification;

final class SendUrlWatchDigestCommand extends Command
{
    protected $signature = 'url-watcher:digest {--hours= : Override the digest window in hours} {--ruleset= : Force a specific configured ruleset key} {--force : Send the digest even when no watch was found}';

    protected $description = 'Send a URL watcher digest notification.';

    public function handle(UrlWatcherSettingsRepositoryInterface $settingsRepository): int
    {
        $settings = $settingsRepository->effectiveSettings([
            'ruleset' => $this->option('ruleset'),
        ]);

        if (! (bool) $settings->digest_enabled) {
            $this->info('URL watcher digest notifications are disabled.');

            return self::SUCCESS;
        }

        $windowHours = max(1, (int) ($this->option('hours') ?: $settings->digest_window_hours ?: 24));
        $since = now()->subHours($windowHours);
        $previousSince = now()->subHours($windowHours * 2);
        $previousUntil = $since;
        $recentEventsLimit = max(1, (int) ($settings->digest_recent_events_limit ?: 10));

        $query = UrlWatch::query()->where('last_seen_at', '>=', $since);
        $eventsQuery = UrlWatchEvent::query()->where('occurred_at', '>=', $since);
        $previousEventsQuery = UrlWatchEvent::query()
            ->where('occurred_at', '>=', $previousSince)
            ->where('occurred_at', '<', $previousUntil);

        $currentHostCounts = (clone $eventsQuery)
            ->selectRaw('COALESCE(NULLIF(host, \'\'), \'-\') as label, COUNT(*) as count')
            ->groupByRaw('COALESCE(NULLIF(host, \'\'), \'-\')')
            ->pluck('count', 'label');

        $previousHostCounts = (clone $previousEventsQuery)
            ->selectRaw('COALESCE(NULLIF(host, \'\'), \'-\') as label, COUNT(*) as count')
            ->groupByRaw('COALESCE(NULLIF(host, \'\'), \'-\')')
            ->pluck('count', 'label');

        $largestSpike = null;

        foreach ($currentHostCounts as $label => $count) {
            $previous = (int) ($previousHostCounts[$label] ?? 0);
            $delta = (int) $count - $previous;

            if ($delta <= 0) {
                continue;
            }

            if ($largestSpike === null || $delta > $largestSpike['delta']) {
                $largestSpike = [
                    'label' => (string) $label,
                    'current' => (int) $count,
                    'previous' => $previous,
                    'delta' => $delta,
                ];
            }
        }

        $currentEventsTotal = (clone $eventsQuery)->count();
        $previousEventsTotal = (clone $previousEventsQuery)->count();

        $summary = [
            'window_hours' => $windowHours,
            'total' => (clone $query)->count(),
            'events_total' => $currentEventsTotal,
            'previous_events_total' => $previousEventsTotal,
            'events_delta' => $currentEventsTotal - $previousEventsTotal,
            'pending' => (clone $query)->where('status', UrlWatchStatus::Pending->value)->count(),
            'reviewed' => (clone $query)->where('status', UrlWatchStatus::Reviewed->value)->count(),
            'archived' => (clone $query)->where('status', UrlWatchStatus::Archived->value)->count(),
            'bots' => (clone $query)->where('classification', UrlWatchClassification::Bot->value)->count(),
            'normal' => (clone $query)->where('classification', UrlWatchClassification::Normal->value)->count(),
            'ignored' => (clone $query)->where('classification', UrlWatchClassification::Ignored->value)->count(),
            'unique_hosts' => (clone $eventsQuery)
                ->selectRaw('COALESCE(NULLIF(host, \'\'), \'-\') as label')
                ->groupByRaw('COALESCE(NULLIF(host, \'\'), \'-\')')
                ->get()
                ->count(),
            'new_watches' => UrlWatch::query()
                ->where('first_seen_at', '>=', $since)
                ->count(),
            'write_attempts' => (clone $eventsQuery)
                ->whereIn('method', ['POST', 'PUT', 'PATCH', 'DELETE'])
                ->count(),
            'server_errors' => (clone $eventsQuery)
                ->where('status_code', '>=', 500)
                ->count(),
            'client_errors' => (clone $eventsQuery)
                ->whereBetween('status_code', [400, 499])
                ->count(),
            'largest_host_spike' => $largestSpike,
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
            'top_methods' => (clone $eventsQuery)
                ->selectRaw('COALESCE(NULLIF(method, \'\'), \'-\') as label, COUNT(*) as count')
                ->groupByRaw('COALESCE(NULLIF(method, \'\'), \'-\')')
                ->orderByDesc('count')
                ->limit(5)
                ->get()
                ->map(static fn (UrlWatchEvent $event): array => [
                    'label' => (string) $event->getAttribute('label'),
                    'count' => (int) $event->getAttribute('count'),
                ])
                ->all(),
            'top_statuses' => (clone $eventsQuery)
                ->selectRaw('COALESCE(CAST(status_code as CHAR), \'-\') as label, COUNT(*) as count')
                ->groupByRaw('COALESCE(CAST(status_code as CHAR), \'-\')')
                ->orderByDesc('count')
                ->limit(5)
                ->get()
                ->map(static fn (UrlWatchEvent $event): array => [
                    'label' => (string) $event->getAttribute('label'),
                    'count' => (int) $event->getAttribute('count'),
                ])
                ->all(),
            'new_paths' => (clone $eventsQuery)
                ->selectRaw('COALESCE(NULLIF(normalized_path, \'\'), path) as label')
                ->groupByRaw('COALESCE(NULLIF(normalized_path, \'\'), path)')
                ->havingRaw('MIN(occurred_at) >= ?', [$since->toDateTimeString()])
                ->orderByRaw('MIN(occurred_at) DESC')
                ->limit(5)
                ->get()
                ->map(static fn (UrlWatchEvent $event): array => [
                    'label' => (string) $event->getAttribute('label'),
                ])
                ->all(),
            'recent_events' => (clone $eventsQuery)
                ->with('urlWatch')
                ->latest('occurred_at')
                ->limit($recentEventsLimit)
                ->get()
                ->map(static function (UrlWatchEvent $event): array {
                    return [
                        'occurred_at' => optional($event->occurred_at)->toDateTimeString(),
                        'method' => (string) ($event->method ?? '-'),
                        'host' => (string) ($event->host ?? '-'),
                        'path' => (string) ($event->normalized_path ?: $event->path ?: '/'),
                        'status_code' => (string) ($event->status_code ?? '-'),
                        'panel' => (string) ($event->panel ?? '-'),
                    ];
                })
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
