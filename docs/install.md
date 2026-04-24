# Install

```bash
composer require proovit/laravel-bot-filter
php artisan vendor:publish --tag=bot-filter-config
php artisan vendor:publish --tag=bot-filter-migrations
php artisan migrate
```

## Activate capture

The package is passive until you register its middleware.

For full coverage, prepend the middleware globally in `bootstrap/app.php`:

```php
use Illuminate\Foundation\Configuration\Middleware;
use Proovit\BotFilter\Http\Middleware\RecordBotProbeMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->prepend([
            RecordBotProbeMiddleware::class,
        ]);
    });
```

That is what records:
- missing routes and probes that end as `404`
- responses ending in `405`
- thrown exceptions that you want to classify as incidents

If you only append it to the `web` group, it may miss true missing-route probes. Global registration is recommended.

### Optional capture tuning

After publishing `config/bot-filter.php`, you can fine tune capture with ignore lists:

- paths: `phpinfo`, `settings.ini`, `wp-login.php`
- hosts: any hostname you want to exclude
- panels: `admin`, `manager`, `b2b`
- methods: `OPTIONS`, `HEAD`
- exception classes: any throwable class you want to ignore

Ignored probes do not create records, but they can still emit `Proovit\BotFilter\Events\BotProbeIgnored` so you can plug your own logging or notification listener.
