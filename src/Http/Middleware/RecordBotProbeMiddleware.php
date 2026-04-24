<?php

declare(strict_types=1);

namespace Proovit\BotFilter\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Proovit\BotFilter\Contracts\BotProbeCapturePolicyInterface;
use Proovit\BotFilter\BotFilter;
use Proovit\BotFilter\Events\BotProbeIgnored;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class RecordBotProbeMiddleware
{
    public function __construct(
        private readonly BotFilter $botFilter,
        private readonly BotProbeCapturePolicyInterface $capturePolicy,
    )
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        if (! (bool) config('bot-filter.enabled', true) || ! (bool) config('bot-filter.capture.enabled', true)) {
            return $next($request);
        }

        try {
            $response = $next($request);
        } catch (Throwable $throwable) {
            if ($this->capturePolicy->shouldCapture($request, $throwable)) {
                $this->botFilter->observe($request, $throwable, [
                    'capture_type' => 'exception',
                ]);
            } elseif (($reason = $this->capturePolicy->ignoreReason($request, $throwable)) !== null) {
                event(new BotProbeIgnored(
                    normalizedPath: $this->normalizePath($request->path()),
                    reason: $reason,
                    context: [
                        'capture_type' => 'exception',
                        'exception_class' => $throwable::class,
                    ],
                ));
            }

            throw $throwable;
        }

        if ($this->capturePolicy->shouldCapture($request, null, $response)) {
            $this->botFilter->observe($request, null, [
                'capture_type' => 'response',
                'http_status' => (int) $response->getStatusCode(),
            ]);
        } elseif (($reason = $this->capturePolicy->ignoreReason($request, null, $response)) !== null && in_array((int) $response->getStatusCode(), (array) config('bot-filter.capture.statuses', [404, 405]), true)) {
            event(new BotProbeIgnored(
                normalizedPath: $this->normalizePath($request->path()),
                reason: $reason,
                context: [
                    'capture_type' => 'response',
                    'http_status' => (int) $response->getStatusCode(),
                ],
            ));
        }

        return $response;
    }

    private function normalizePath(string $path): string
    {
        $path = parse_url($path, PHP_URL_PATH) ?: $path;
        $path = strtolower(trim($path));
        $path = ltrim($path, '/');

        return $path === '' ? '/' : $path;
    }
}
