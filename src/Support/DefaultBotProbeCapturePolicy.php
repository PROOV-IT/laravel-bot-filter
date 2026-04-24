<?php

declare(strict_types=1);

namespace Proovit\BotFilter\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Proovit\BotFilter\Contracts\BotProbeCapturePolicyInterface;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class DefaultBotProbeCapturePolicy implements BotProbeCapturePolicyInterface
{
    public function shouldCapture(Request $request, ?Throwable $throwable = null, ?Response $response = null): bool
    {
        if (! (bool) config('bot-filter.enabled', true) || ! (bool) config('bot-filter.capture.enabled', true)) {
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
        if (! (bool) config('bot-filter.enabled', true) || ! (bool) config('bot-filter.capture.enabled', true)) {
            return 'capture_disabled';
        }

        $ignore = (array) config('bot-filter.capture.ignore', []);
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

        if ($throwable !== null && ! (bool) config('bot-filter.capture.exceptions', true)) {
            return 'exception_capture_disabled';
        }

        if ($throwable === null && $response !== null) {
            $statuses = array_map(
                static fn ($value): int => (int) $value,
                (array) config('bot-filter.capture.statuses', [404, 405])
            );

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
