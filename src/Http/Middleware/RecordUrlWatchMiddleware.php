<?php

declare(strict_types=1);

namespace Proovit\UrlWatcher\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Proovit\UrlWatcher\Contracts\UrlWatchCapturePolicyInterface;
use Proovit\UrlWatcher\Events\UrlWatchIgnored;
use Proovit\UrlWatcher\UrlWatcher;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class RecordUrlWatchMiddleware
{
    public function __construct(
        private readonly UrlWatcher $botFilter,
        private readonly UrlWatchCapturePolicyInterface $capturePolicy,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! (bool) config('url-watcher.enabled', true) || ! (bool) config('url-watcher.capture.enabled', true)) {
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
                event(new UrlWatchIgnored(
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
        } elseif (($reason = $this->capturePolicy->ignoreReason($request, null, $response)) !== null && in_array((int) $response->getStatusCode(), (array) config('url-watcher.capture.statuses', [404, 405]), true)) {
            event(new UrlWatchIgnored(
                normalizedPath: $this->normalizePath($request->path()),
                reason: $reason,
                context: [
                    'capture_type' => 'response',
                    'http_status' => (int) $response->getStatusCode(),
                    'request_id' => $request->headers->get('x-request-id'),
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
