<?php

declare(strict_types=1);

namespace Proovit\BotFilter;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Proovit\BotFilter\Contracts\BotProbeClassifierInterface;
use Proovit\BotFilter\Contracts\BotProbeNotifierInterface;
use Proovit\BotFilter\Contracts\BotProbeRepositoryInterface;
use Proovit\BotFilter\Enums\BotProbeClassification;
use Proovit\BotFilter\Enums\BotProbeStatus;
use Proovit\BotFilter\Events\BotProbeRecorded;
use Proovit\BotFilter\Models\BotProbe;
use Proovit\BotFilter\Support\BotProbeObservation;
use Throwable;

final class BotFilter
{
    public function __construct(
        private readonly BotProbeRepositoryInterface $repository,
        private readonly BotProbeClassifierInterface $classifier,
        private readonly BotProbeNotifierInterface $notifier,
    ) {
    }

    public function observe(Request $request, ?Throwable $throwable = null, array $meta = []): BotProbe
    {
        $context = $this->contextFromRequest($request, $throwable, $meta);

        return $this->record($context)->probe;
    }

    public function record(array $context): BotProbeObservation
    {
        $definition = $this->classifier->match((string) ($context['normalized_path'] ?? $context['path'] ?? '/'));
        $observation = $this->repository->record($context, $definition);

        event(new BotProbeRecorded($observation));

        if ($observation->probe->shouldNotify()) {
            $this->notifier->notify($observation->probe);
            $observation->probe->markAsNotified();
        }

        return $observation;
    }

    public function classify(BotProbe $probe, BotProbeClassification $classification): BotProbe
    {
        $probe->classification = $classification;
        $probe->status = BotProbeStatus::Reviewed;
        $probe->save();

        event(new \Proovit\BotFilter\Events\BotProbeClassified($probe));

        return $probe;
    }

    private function contextFromRequest(Request $request, ?Throwable $throwable = null, array $meta = []): array
    {
        $path = trim((string) $request->path());
        $normalizedPath = strtolower(ltrim(parse_url($path, PHP_URL_PATH) ?: $path, '/'));

        return array_filter([
            'exception_class' => $throwable ? $throwable::class : null,
            'method' => $request->method(),
            'host' => $request->getHost(),
            'path' => '/'.ltrim($path, '/'),
            'normalized_path' => $normalizedPath === '' ? '/' : $normalizedPath,
            'route_name' => optional($request->route())->getName(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'panel' => $this->guessPanel($request->path()),
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
        if (! (bool) config('bot-filter.defaults.track_request_headers', true)) {
            return [];
        }

        return [
            'accept' => $request->header('accept'),
            'referer' => $request->header('referer'),
            'sec_fetch_site' => $request->header('sec-fetch-site'),
        ];
    }
}
