<?php

declare(strict_types=1);

namespace Proovit\UrlWatcher;

use Illuminate\Http\Request;
use Proovit\UrlWatcher\Contracts\UrlWatchClassifierInterface;
use Proovit\UrlWatcher\Contracts\UrlWatchNotifierInterface;
use Proovit\UrlWatcher\Contracts\UrlWatchRepositoryInterface;
use Proovit\UrlWatcher\Enums\UrlWatchClassification;
use Proovit\UrlWatcher\Enums\UrlWatchStatus;
use Proovit\UrlWatcher\Events\UrlWatchClassified;
use Proovit\UrlWatcher\Events\UrlWatchRecorded;
use Proovit\UrlWatcher\Models\UrlWatch;
use Proovit\UrlWatcher\Support\UrlWatchObservation;
use Throwable;

final class UrlWatcher
{
    public function __construct(
        private readonly UrlWatchRepositoryInterface $repository,
        private readonly UrlWatchClassifierInterface $classifier,
        private readonly UrlWatchNotifierInterface $notifier,
    ) {}

    public function observe(Request $request, ?Throwable $throwable = null, array $meta = []): UrlWatch
    {
        $context = $this->contextFromRequest($request, $throwable, $meta);

        return $this->record($context)->watch;
    }

    public function record(array $context): UrlWatchObservation
    {
        $definition = $this->classifier->match((string) ($context['normalized_path'] ?? $context['path'] ?? '/'));
        $observation = $this->repository->record($context, $definition);

        event(new UrlWatchRecorded($observation));

        if ($observation->watch->shouldNotify()) {
            $this->notifier->notify($observation->watch);
            $observation->watch->markAsNotified();
        }

        return $observation;
    }

    public function classify(UrlWatch $watch, UrlWatchClassification $classification): UrlWatch
    {
        $watch->classification = $classification;
        $watch->status = UrlWatchStatus::Reviewed;
        $watch->save();

        event(new UrlWatchClassified($watch));

        return $watch;
    }

    private function contextFromRequest(Request $request, ?Throwable $throwable = null, array $meta = []): array
    {
        $path = trim((string) $request->path());
        $normalizedPath = strtolower(ltrim(parse_url($path, PHP_URL_PATH) ?: $path, '/'));

        return array_filter([
            'exception_class' => $throwable ? $throwable::class : null,
            'exception_message' => $throwable?->getMessage(),
            'method' => $request->method(),
            'host' => $request->getHost(),
            'path' => '/'.ltrim($path, '/'),
            'normalized_path' => $normalizedPath === '' ? '/' : $normalizedPath,
            'full_url' => $request->fullUrl(),
            'query_string' => (string) $request->getQueryString(),
            'route_name' => optional($request->route())->getName(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'panel' => $this->guessPanel($request->path()),
            'request_id' => $request->headers->get('x-request-id'),
            'occurred_at' => now(),
            'meta' => array_merge($meta, $this->trackableRequestMeta($request)),
        ], static fn ($value) => ! is_null($value));
    }

    private function guessPanel(string $path): ?string
    {
        return match (true) {
            str_starts_with($path, 'admin') => 'admin',
            str_starts_with($path, 'manager') => 'manager',
            str_starts_with($path, 'b2b') => 'b2b',
            default => null,
        };
    }

    private function trackableRequestMeta(Request $request): array
    {
        if (! (bool) config('url-watcher.defaults.track_request_headers', true)) {
            return [];
        }

        return [
            'accept' => $request->header('accept'),
            'referer' => $request->header('referer'),
            'sec_fetch_site' => $request->header('sec-fetch-site'),
        ];
    }
}
