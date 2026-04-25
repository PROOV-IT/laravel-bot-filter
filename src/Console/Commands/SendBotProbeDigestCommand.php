<?php

declare(strict_types=1);

namespace Proovit\BotFilter\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;
use Proovit\BotFilter\Contracts\BotFilterSettingsRepositoryInterface;
use Proovit\BotFilter\Enums\BotProbeClassification;
use Proovit\BotFilter\Enums\BotProbeStatus;
use Proovit\BotFilter\Models\BotProbe;
use Proovit\BotFilter\Notifications\BotProbeDigestNotification;

final class SendBotProbeDigestCommand extends Command
{
    protected $signature = 'bot-filter:digest {--hours= : Override the digest window in hours} {--force : Send the digest even when no probe was found}';

    protected $description = 'Send a bot filter digest notification.';

    public function handle(BotFilterSettingsRepositoryInterface $settingsRepository): int
    {
        $settings = $settingsRepository->effectiveSettings();

        if (! (bool) $settings->digest_enabled) {
            $this->info('Bot filter digest notifications are disabled.');

            return self::SUCCESS;
        }

        $windowHours = max(1, (int) ($this->option('hours') ?: $settings->digest_window_hours ?: 24));
        $since = now()->subHours($windowHours);

        $query = BotProbe::query()->where('last_seen_at', '>=', $since);

        $summary = [
            'window_hours' => $windowHours,
            'total' => (clone $query)->count(),
            'pending' => (clone $query)->where('status', BotProbeStatus::Pending->value)->count(),
            'reviewed' => (clone $query)->where('status', BotProbeStatus::Reviewed->value)->count(),
            'archived' => (clone $query)->where('status', BotProbeStatus::Archived->value)->count(),
            'bots' => (clone $query)->where('classification', BotProbeClassification::Bot->value)->count(),
            'normal' => (clone $query)->where('classification', BotProbeClassification::Normal->value)->count(),
            'ignored' => (clone $query)->where('classification', BotProbeClassification::Ignored->value)->count(),
            'top_paths' => (clone $query)
                ->selectRaw('COALESCE(NULLIF(normalized_path, \'\'), path) as label, COUNT(*) as count')
                ->groupByRaw('COALESCE(NULLIF(normalized_path, \'\'), path)')
                ->orderByDesc('count')
                ->limit(10)
                ->get()
                ->map(static fn (BotProbe $probe): array => [
                    'label' => (string) $probe->getAttribute('label'),
                    'count' => (int) $probe->getAttribute('count'),
                ])
                ->all(),
            'top_hosts' => (clone $query)
                ->selectRaw('COALESCE(NULLIF(host, \'\'), \'-\') as label, COUNT(*) as count')
                ->groupByRaw('COALESCE(NULLIF(host, \'\'), \'-\')')
                ->orderByDesc('count')
                ->limit(10)
                ->get()
                ->map(static fn (BotProbe $probe): array => [
                    'label' => (string) $probe->getAttribute('label'),
                    'count' => (int) $probe->getAttribute('count'),
                ])
                ->all(),
        ];

        if ($summary['total'] === 0 && ! (bool) $settings->digest_notify_when_empty) {
            $this->info('No bot probes found in the selected digest window.');

            return self::SUCCESS;
        }

        $target = (string) ($settings->digest_mail ?: config('app.admin_email', 'contact@proov-it.io'));

        Notification::route('mail', $target)
            ->notify(new BotProbeDigestNotification(
                $summary,
                filled($settings->digest_title ?? null) ? (string) $settings->digest_title : null,
                filled($settings->digest_intro ?? null) ? (string) $settings->digest_intro : null,
            ));

        $this->info(sprintf(
            'Sent bot filter digest to %s for the last %d hour(s).',
            $target,
            $windowHours,
        ));

        return self::SUCCESS;
    }
}
