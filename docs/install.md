# Install

```bash
composer require proovit/laravel-bot-filter
php artisan vendor:publish --tag=bot-filter-config
php artisan vendor:publish --tag=bot-filter-migrations
php artisan migrate
```

The migrations create both the probe registry table and the bot filter settings table used by the Filament settings page.

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

### Notification modes

The core package notification is controlled by the runtime settings row:

- `notifications_enabled`: master switch
- `notification_mode`: `default` or `custom`
- `notification_title`: optional subject for the package notification
- `notification_intro`: optional intro copy displayed before the bot details
- `custom_notification_class`: optional application notification class to use when `notification_mode` is `custom`

If the custom class cannot be resolved, the package falls back to its built-in notification. When the built-in notification is used, the title and intro copy from the runtime settings are applied before the bot probe details.
