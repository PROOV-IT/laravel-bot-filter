<?php

declare(strict_types=1);

namespace Proovit\UrlWatcher\Support;

use Illuminate\Support\Facades\DB;
use Proovit\UrlWatcher\Contracts\UrlWatchFingerprintResolverInterface;
use Proovit\UrlWatcher\Contracts\UrlWatchRepositoryInterface;
use Proovit\UrlWatcher\Enums\UrlWatchClassification;
use Proovit\UrlWatcher\Enums\UrlWatchStatus;
use Proovit\UrlWatcher\Models\UrlWatch;
use Proovit\UrlWatcher\Models\UrlWatchEvent;

final class DefaultUrlWatchRepository implements UrlWatchRepositoryInterface
{
    public function __construct(
        private readonly UrlWatchFingerprintResolverInterface $fingerprintResolver,
    ) {}

    public function record(array $context, ?UrlWatchDefinition $definition = null): UrlWatchObservation
    {
        $context['normalized_path'] = $context['normalized_path'] ?? $this->normalizePath((string) ($context['path'] ?? '/'));
        $context['fingerprint'] = $context['fingerprint'] ?? $this->fingerprintResolver->resolve($context);

        return DB::connection((string) config('url-watcher.database.connection') ?: null)
            ->transaction(function () use ($context, $definition): UrlWatchObservation {
                $watch = UrlWatch::query()
                    ->where('fingerprint', $context['fingerprint'])
                    ->lockForUpdate()
                    ->first();

                $created = false;

                if (! $watch) {
                    $watch = new UrlWatch;
                    $watch->fingerprint = $context['fingerprint'];
                    $watch->count = 0;
                    $watch->first_seen_at = now();
                    $watch->status = UrlWatchStatus::Pending;
                    $watch->classification = UrlWatchClassification::Unknown;
                    $created = true;
                }

                $watch->fill([
                    'exception_class' => $context['exception_class'] ?? $watch->exception_class,
                    'method' => $context['method'] ?? $watch->method,
                    'host' => $context['host'] ?? $watch->host,
                    'path' => $context['path'] ?? $watch->path,
                    'normalized_path' => $context['normalized_path'] ?? $watch->normalized_path,
                    'route_name' => $context['route_name'] ?? $watch->route_name,
                    'ip' => $context['ip'] ?? $watch->ip,
                    'user_agent' => $context['user_agent'] ?? $watch->user_agent,
                    'panel' => $context['panel'] ?? $watch->panel,
                    'meta' => array_replace_recursive(
                        (array) ($watch->meta ?? []),
                        $context['meta'] ?? []
                    ),
                    'suggested_classification' => $definition?->suggestedClassification ?? $watch->suggested_classification,
                ]);

                $watch->count = $created ? 1 : ((int) $watch->count + 1);
                $watch->last_seen_at = now();
                $watch->save();

                $event = $this->recordEvent($watch, $context);

                return new UrlWatchObservation($watch->fresh(), $event, $created, $definition);
            });
    }

    public function findByFingerprint(string $fingerprint): ?UrlWatch
    {
        return UrlWatch::query()->where('fingerprint', $fingerprint)->first();
    }

    private function normalizePath(string $path): string
    {
        $path = parse_url($path, PHP_URL_PATH) ?: $path;
        $path = strtolower(trim($path));
        $path = ltrim($path, '/');

        return $path === '' ? '/' : $path;
    }

    private function recordEvent(UrlWatch $watch, array $context): UrlWatchEvent
    {
        $meta = (array) ($context['meta'] ?? []);

        return UrlWatchEvent::query()->create([
            'url_watch_id' => $watch->getKey(),
            'method' => $context['method'] ?? null,
            'host' => $context['host'] ?? null,
            'path' => $context['path'] ?? null,
            'normalized_path' => $context['normalized_path'] ?? null,
            'full_url' => $context['full_url'] ?? null,
            'query_string' => (bool) config('url-watcher.history.store_query_string', true) ? ($context['query_string'] ?? null) : null,
            'route_name' => $context['route_name'] ?? null,
            'ip' => $context['ip'] ?? null,
            'user_agent' => (bool) config('url-watcher.history.store_user_agent', true) ? ($context['user_agent'] ?? null) : null,
            'referer' => (bool) config('url-watcher.history.store_referer', true) ? ($meta['referer'] ?? null) : null,
            'panel' => $context['panel'] ?? null,
            'status_code' => $meta['http_status'] ?? null,
            'exception_class' => $context['exception_class'] ?? null,
            'exception_message' => $context['exception_message'] ?? null,
            'request_id' => $context['request_id'] ?? null,
            'occurred_at' => $context['occurred_at'] ?? now(),
            'meta' => $meta,
        ]);
    }
}
