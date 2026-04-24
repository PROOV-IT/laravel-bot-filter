# proovit/laravel-bot-filter

Laravel 12 core package for recording, classifying, and deduplicating bot and probe incidents.

## What it does

- stores route scan and bot probe incidents in a normalized table
- deduplicates repeated probes with a stable fingerprint
- emits events when a probe is recorded or reclassified
- sends a first-alert notification once, then only increments the counter
- exposes a fluent API for request and exception observation

## Install

```bash
composer require proovit/laravel-bot-filter
```

## Documentation

- [Install](docs/install.md)
- [Configuration](docs/configuration.md)
- [Probe catalog](docs/probes.md)
- [Events](docs/events.md)
- [Release notes](docs/release-notes.md)

