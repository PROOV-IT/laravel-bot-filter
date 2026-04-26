# Release notes

## 0.3.4

- Added ruleset-aware anomaly digest reporting and hardened runtime behavior for the latest URL watcher workflows

## 0.1.0

- initial core scaffolding
- normalized URL definition catalog
- event-driven repository and notification flow

## 0.2.0

- ruleset-aware runtime settings
- digest notification command
- notification subject / intro customization from runtime settings

## 0.3.0

- richer digest with top hosts, status/method breakdowns, and recent events
- retention settings and pruning command
- hardened settings migrations for fresh installs and incremental upgrades

## 0.3.1

- ruleset-forced digest and prune commands via `--ruleset`
- anomaly-oriented digest metrics such as event delta, unique hosts, write attempts, and host spikes
