# Configuration

The package is configured through `config/bot-filter.php`.

Important keys:

- `enabled`: master switch
- `database.table`: incident table name
- `database.connection`: optional dedicated connection
- `settings.table`: runtime settings table name
- `notification.mode`: default or custom notification strategy
- `notification.title`: optional subject used by the package notification
- `notification.intro`: optional intro copy displayed before the probe details in the package notification
- `notification.mail`: first-alert recipient
- `notification.custom_notification_class`: optional application notification class used in custom mode
- `probes`: normalized probe catalog

Capture settings:

- `capture.enabled`: master switch for request capture
- `capture.exceptions`: record thrown exceptions before rethrowing them
- `capture.statuses`: response codes that should be stored as incidents, by default `404` and `405`
- `capture.ignore.paths`: list of exact values or wildcard patterns that should never be captured
- `capture.ignore.hosts`: hostnames to exclude from capture
- `capture.ignore.panels`: panel names to exclude from capture
- `capture.ignore.methods`: HTTP methods to exclude from capture
- `capture.ignore.exception_classes`: exception classes to exclude from capture

Recommended integration:

- register `Proovit\BotFilter\Http\Middleware\RecordBotProbeMiddleware` globally in `bootstrap/app.php`
- keep the package enabled, but control what is captured with the config flags above
- listen to `Proovit\BotFilter\Events\BotProbeIgnored` if you want to log filtered probes or feed a review queue

Runtime settings are stored in the `bot_filter_settings` table by default and can be managed from the Filament plugin settings page.
