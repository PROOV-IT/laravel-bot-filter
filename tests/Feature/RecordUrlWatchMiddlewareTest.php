<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Proovit\UrlWatcher\Events\UrlWatchIgnored;
use Proovit\UrlWatcher\Http\Middleware\RecordUrlWatchMiddleware;
use Proovit\UrlWatcher\Models\UrlWatch;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

it('records a 404 response probe through the middleware', function (): void {
    Notification::fake();

    $middleware = app(RecordUrlWatchMiddleware::class);
    $request = Request::create('/wp-login.php', 'GET', [], [], [], [
        'HTTP_HOST' => 'api.proov-it.online',
        'HTTP_USER_AGENT' => 'Bot',
    ]);

    $response = $middleware->handle($request, static fn (): Response => new Response('', 404));

    expect($response->getStatusCode())->toBe(404);
    expect(UrlWatch::query()->count())->toBe(1);
    expect(UrlWatch::query()->first()?->normalized_path)->toBe('wp-login.php');
});

it('records thrown exceptions before rethrowing them', function (): void {
    $middleware = app(RecordUrlWatchMiddleware::class);
    $request = Request::create('/robots.txt', 'GET', [], [], [], [
        'HTTP_HOST' => 'api.proov-it.online',
        'HTTP_USER_AGENT' => 'Bot',
    ]);

    try {
        $middleware->handle($request, static function (): Response {
            throw new RouteNotFoundException('missing route');
        });
    } catch (RouteNotFoundException) {
        //
    }

    expect(UrlWatch::query()->count())->toBe(1);
    expect(UrlWatch::query()->first()?->normalized_path)->toBe('robots.txt');
});

it('ignores configured probes and emits an ignored event', function (): void {
    Event::fake([UrlWatchIgnored::class]);
    Notification::fake();
    config()->set('url-watcher.capture.ignore.paths', ['phpinfo']);

    $middleware = app(RecordUrlWatchMiddleware::class);
    $request = Request::create('/phpinfo', 'GET', [], [], [], [
        'HTTP_HOST' => 'api.proov-it.online',
        'HTTP_USER_AGENT' => 'Bot',
    ]);

    $response = $middleware->handle($request, static fn (): Response => new Response('', 404));

    expect($response->getStatusCode())->toBe(404);
    expect(UrlWatch::query()->count())->toBe(0);

    Event::assertDispatched(UrlWatchIgnored::class, static function (UrlWatchIgnored $event): bool {
        return $event->normalizedPath === 'phpinfo'
            && $event->reason === 'ignored_path';
    });
});
