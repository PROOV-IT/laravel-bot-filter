<?php

declare(strict_types=1);

namespace Proovit\BotFilter\Support;

use Illuminate\Support\Facades\DB;
use Proovit\BotFilter\Contracts\BotProbeFingerprintResolverInterface;
use Proovit\BotFilter\Contracts\BotProbeRepositoryInterface;
use Proovit\BotFilter\Enums\BotProbeClassification;
use Proovit\BotFilter\Enums\BotProbeStatus;
use Proovit\BotFilter\Models\BotProbe;

final class DefaultBotProbeRepository implements BotProbeRepositoryInterface
{
    public function __construct(
        private readonly BotProbeFingerprintResolverInterface $fingerprintResolver,
    ) {
    }

    public function record(array $context, ?BotProbeDefinition $definition = null): BotProbeObservation
    {
        $context['normalized_path'] = $context['normalized_path'] ?? $this->normalizePath((string) ($context['path'] ?? '/'));
        $context['fingerprint'] = $context['fingerprint'] ?? $this->fingerprintResolver->resolve($context);

        return DB::connection((string) config('bot-filter.database.connection') ?: null)
            ->transaction(function () use ($context, $definition): BotProbeObservation {
                $probe = BotProbe::query()
                    ->where('fingerprint', $context['fingerprint'])
                    ->lockForUpdate()
                    ->first();

                $created = false;

                if (! $probe) {
                    $probe = new BotProbe();
                    $probe->fingerprint = $context['fingerprint'];
                    $probe->count = 0;
                    $probe->first_seen_at = now();
                    $probe->status = BotProbeStatus::Pending;
                    $probe->classification = BotProbeClassification::Unknown;
                    $created = true;
                }

                $probe->fill([
                    'exception_class' => $context['exception_class'] ?? $probe->exception_class,
                    'method' => $context['method'] ?? $probe->method,
                    'host' => $context['host'] ?? $probe->host,
                    'path' => $context['path'] ?? $probe->path,
                    'normalized_path' => $context['normalized_path'] ?? $probe->normalized_path,
                    'route_name' => $context['route_name'] ?? $probe->route_name,
                    'ip' => $context['ip'] ?? $probe->ip,
                    'user_agent' => $context['user_agent'] ?? $probe->user_agent,
                    'panel' => $context['panel'] ?? $probe->panel,
                    'meta' => array_replace_recursive(
                        (array) ($probe->meta ?? []),
                        $context['meta'] ?? []
                    ),
                    'suggested_classification' => $definition?->suggestedClassification ?? $probe->suggested_classification,
                ]);

                $probe->count = $created ? 1 : ((int) $probe->count + 1);
                $probe->last_seen_at = now();
                $probe->save();

                return new BotProbeObservation($probe->fresh(), $created, $definition);
            });
    }

    public function findByFingerprint(string $fingerprint): ?BotProbe
    {
        return BotProbe::query()->where('fingerprint', $fingerprint)->first();
    }

    private function normalizePath(string $path): string
    {
        $path = parse_url($path, PHP_URL_PATH) ?: $path;
        $path = strtolower(trim($path));
        $path = ltrim($path, '/');

        return $path === '' ? '/' : $path;
    }
}
