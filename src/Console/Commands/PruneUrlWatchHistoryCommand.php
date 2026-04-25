<?php

declare(strict_types=1);

namespace Proovit\UrlWatcher\Console\Commands;

use Illuminate\Console\Command;
use Proovit\UrlWatcher\Contracts\UrlWatcherSettingsRepositoryInterface;
use Proovit\UrlWatcher\Enums\UrlWatchStatus;
use Proovit\UrlWatcher\Models\UrlWatch;
use Proovit\UrlWatcher\Models\UrlWatchEvent;

final class PruneUrlWatchHistoryCommand extends Command
{
    protected $signature = 'url-watcher:prune {--days= : Override the retention window in days} {--with-aggregates : Also prune archived aggregate watches with no remaining events}';

    protected $description = 'Prune old URL watcher history events and optional archived aggregate records.';

    public function handle(UrlWatcherSettingsRepositoryInterface $settingsRepository): int
    {
        $settings = $settingsRepository->effectiveSettings();

        if (! (bool) $settings->retention_enabled && ! filled($this->option('days'))) {
            $this->info('URL watcher retention is disabled.');

            return self::SUCCESS;
        }

        $days = max(1, (int) ($this->option('days') ?: $settings->retention_days ?: 30));
        $threshold = now()->subDays($days);

        $deletedEvents = UrlWatchEvent::query()
            ->where('occurred_at', '<', $threshold)
            ->delete();

        $deletedAggregates = 0;

        if ((bool) ($this->option('with-aggregates') ?: $settings->retention_prune_aggregates)) {
            $deletedAggregates = UrlWatch::query()
                ->whereIn('status', [
                    UrlWatchStatus::Archived->value,
                    UrlWatchStatus::Reviewed->value,
                ])
                ->where('last_seen_at', '<', $threshold)
                ->whereDoesntHave('events')
                ->delete();
        }

        $this->info(sprintf(
            'Pruned %d URL watch event(s) older than %d day(s)%s.',
            $deletedEvents,
            $days,
            $deletedAggregates > 0 ? sprintf(' and %d aggregate watch(es)', $deletedAggregates) : '',
        ));

        return self::SUCCESS;
    }
}
