<?php

declare(strict_types=1);

namespace Proovit\BotFilter\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Proovit\BotFilter\Contracts\BotFilterSettingsRepositoryInterface;
use Proovit\BotFilter\Contracts\BotProbeCapturePolicyInterface;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class DefaultBotProbeCapturePolicy implements BotProbeCapturePolicyInterface
{
    public function __construct(private readonly BotFilterSettingsRepositoryInterface $settings) {}

    public function shouldCapture(Request $request, ?Throwable $throwable = null, ?Response $response = null): bool
    {
        $settings = $this->settings->settings();

        if (! (bool) config('bot-filter.enabled', true) || ! (bool) $settings->capture_enabled) {
            return false;
        }

        if ($this->ignoreReason($request, $throwable, $response) !== null) {
            return false;
        }

        if ($throwable !== null) {
            return (bool) config('bot-filter.capture.exceptions', true);
        }

        if ($response === null) {
            return false;
        }

        return in_array(
            (int) $response->getStatusCode(),
            array_map(static fn ($value): int => (int) $value, (array) config('bot-filter.capture.statuses', [404, 405])),
            true
        );
    }

    public function ignoreReason(Request $request, ?Throwable $throwable = null, ?Response $response = null): ?string
    {
        $settings = $this->settings->settings();

        if (! (bool) config('bot-filter.enabled', true) || ! (bool) $settings->capture_enabled) {
            return 'capture_disabled';
        }

        $ignore = [
            'paths' => (array) $settings->ignore_paths,
            'hosts' => (array) $settings->ignore_hosts,
            'panels' => (array) $settings->ignore_panels,
            'methods' => (array) $settings->ignore_methods,
            'exception_classes' => (array) $settings->ignore_exception_classes,
        ];
        $normalizedPath = $this->normalizePath($request->path());
        $host = strtolower(trim($request->getHost()));
        $panel = $this->guessPanel($request->path());
        $method = strtoupper($request->method());
        $exceptionClass = $throwable ? $throwable::class : null;

        if ($this->matchesAny((array) ($ignore['hosts'] ?? []), $host)) {
            return 'ignored_host';
        }

        if ($panel !== null && $this->matchesAny((array) ($ignore['panels'] ?? []), $panel)) {
            return 'ignored_panel';
        }

        if ($this->matchesAny((array) ($ignore['methods'] ?? []), $method)) {
            return 'ignored_method';
        }

        if ($this->matchesAny((array) ($ignore['paths'] ?? []), $normalizedPath)) {
            return 'ignored_path';
        }

        if ($exceptionClass !== null && $this->matchesAny((array) ($ignore['exception_classes'] ?? []), $exceptionClass)) {
            return 'ignored_exception_class';
        }

        if ($throwable !== null && ! (bool) $settings->capture_exceptions) {
            return 'exception_capture_disabled';
        }

        if ($throwable === null && $response !== null) {
            $statuses = array_map(static fn ($value): int => (int) $value, (array) $settings->capture_statuses);

            if (! in_array((int) $response->getStatusCode(), $statuses, true)) {
                return 'status_not_listed';
            }
        }

        return null;
    }

    private function normalizePath(string $path): string
    {
        $path = parse_url($path, PHP_URL_PATH) ?: $path;
        $path = strtolower(trim($path));
        $path = ltrim($path, '/');

        return $path === '' ? '/' : $path;
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

    private function guessPanel(string $path): ?string
    {
        return match (true) {
            str_starts_with($path, 'admin') => 'admin',
            str_starts_with($path, 'manager') => 'manager',
            str_starts_with($path, 'b2b') => 'b2b',
            default => null,
        };
    }
}
