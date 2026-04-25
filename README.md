# proovit/laravel-url-watcher

Laravel 12 core package for recording, classifying, and deduplicating problematic URL incidents.

## What it does

- stores aggregated URL watches in a normalized table
- stores detailed URL watch events in a dedicated history table
- stores runtime URL watcher settings in a dedicated table
- deduplicates repeated incidents with a stable fingerprint
- emits events when a URL watch is recorded or reclassified
- emits an ignored event when a known incident is intentionally filtered out
- sends a first-alert notification once, then only increments the counter
- supports context-aware rulesets for environment, host, and panel overrides
- ships with a digest notification command for scheduled URL watch summaries
- ships with a retention command for pruning old event history
- lets you customize the default notification subject and intro copy
- exposes a fluent API for request and exception observation
- ships a request capture middleware, but you must register it in your application bootstrap
- supports configurable ignore lists for paths, hosts, panels, methods, and exception classes
- supports configurable retention and richer digests with top hosts and recent events

## Install

```bash
composer require proovit/laravel-url-watcher
```

## Documentation

- [Install](docs/install.md)
- [Configuration](docs/configuration.md)
- [Definition catalog](docs/probes.md)
- [Events](docs/events.md)
- [Release notes](docs/release-notes.md)
