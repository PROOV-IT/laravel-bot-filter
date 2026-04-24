# Configuration

The package is configured through `config/bot-filter.php`.

Important keys:

- `enabled`: master switch
- `database.table`: incident table name
- `database.connection`: optional dedicated connection
- `notification.mail`: first-alert recipient
- `probes`: normalized probe catalog

Capture settings:

- `capture.enabled`: master switch for request capture
- `capture.exceptions`: record thrown exceptions before rethrowing them
- `capture.statuses`: response codes that should be stored as incidents, by default `404` and `405`

Recommended integration:

- register `Proovit\BotFilter\Http\Middleware\RecordBotProbeMiddleware` globally in `bootstrap/app.php`
- keep the package enabled, but control what is captured with the config flags above
