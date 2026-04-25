# Configuration

The package is configured through `config/url-watcher.php`.

Important keys:

- `enabled`: master switch
- `database.table`: incident table name
- `database.connection`: optional dedicated connection
- `settings.table`: runtime settings table name
- `notification.mode`: default or custom notification strategy
- `notification.title`: optional subject used by the package notification
- `notification.intro`: optional intro copy displayed before the incident details in the package notification
- `notification.mail`: first-alert recipient
- `notification.custom_notification_class`: optional application notification class used in custom mode
- `rulesets`: optional context-aware overrides for environment, host, and panel
- `digest.enabled`: enable the periodic digest notification
- `digest.mail`: digest recipient
- `digest.title`: custom digest subject
- `digest.intro`: intro copy displayed before the digest summary
- `digest.window_hours`: digest lookback window
- `digest.notify_when_empty`: send an empty digest when the window has no incidents
- `digest.recent_events_limit`: number of recent events included in the digest
- `retention.enabled`: enable the retention workflow
- `retention.days`: keep events for this many days before pruning
- `retention.prune_aggregates`: also delete archived/reviewed aggregates with no remaining events
- `definitions`: normalized URL definition catalog
- `history.enabled`: enable detailed event history storage
- `history.store_user_agent`: keep the user agent on event rows
- `history.store_query_string`: keep the query string on event rows
- `history.store_referer`: keep the referer on event rows

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

- register `Proovit\UrlWatcher\Http\Middleware\RecordUrlWatchMiddleware` globally in `bootstrap/app.php`
- keep the package enabled, but control what is captured with the config flags above
- listen to `Proovit\UrlWatcher\Events\UrlWatchIgnored` if you want to log filtered incidents or feed a review queue

Runtime settings are stored in the `url_watcher_settings` table by default and can be managed from the Filament plugin settings page. Detailed request history is stored in `url_watch_events`.

### Rulesets

Rulesets allow you to override runtime settings depending on the current environment, host, or panel.

Each ruleset can define:

- `key`
- `label`
- `enabled`
- `environments`
- `hosts`
- `panels`
- `overrides`

The runtime settings page also allows you to force a specific active ruleset with `active_ruleset`.
