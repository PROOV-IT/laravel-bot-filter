<?php

declare(strict_types=1);

namespace Proovit\BotFilter\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Proovit\BotFilter\BotFilter;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class RecordBotProbeMiddleware
{
    public function __construct(private readonly BotFilter $botFilter)
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
            if ((bool) config('bot-filter.capture.exceptions', true)) {
                $this->botFilter->observe($request, $throwable, [
                    'capture_type' => 'exception',
                ]);
            }

            throw $throwable;
        }

        if ($this->shouldRecordStatus((int) $response->getStatusCode())) {
            $this->botFilter->observe($request, null, [
                'capture_type' => 'response',
                'http_status' => (int) $response->getStatusCode(),
            ]);
        }

        return $response;
    }

    private function shouldRecordStatus(int $statusCode): bool
    {
        $statuses = array_map(
            static fn ($value): int => (int) $value,
            (array) config('bot-filter.capture.statuses', [404, 405])
        );

        return in_array($statusCode, $statuses, true);
    }
}
