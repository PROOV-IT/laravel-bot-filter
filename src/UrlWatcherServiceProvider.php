<?php

declare(strict_types=1);

namespace Proovit\UrlWatcher;

use Illuminate\Support\ServiceProvider;
use Proovit\UrlWatcher\Console\Commands\PruneUrlWatchHistoryCommand;
use Proovit\UrlWatcher\Console\Commands\SendUrlWatchDigestCommand;
use Proovit\UrlWatcher\Contracts\UrlWatchCapturePolicyInterface;
use Proovit\UrlWatcher\Contracts\UrlWatchClassifierInterface;
use Proovit\UrlWatcher\Contracts\UrlWatcherSettingsRepositoryInterface;
use Proovit\UrlWatcher\Contracts\UrlWatchFingerprintResolverInterface;
use Proovit\UrlWatcher\Contracts\UrlWatchNotifierInterface;
use Proovit\UrlWatcher\Contracts\UrlWatchRepositoryInterface;
use Proovit\UrlWatcher\Support\DefaultUrlWatchCapturePolicy;
use Proovit\UrlWatcher\Support\DefaultUrlWatchClassifier;
use Proovit\UrlWatcher\Support\DefaultUrlWatcherSettingsRepository;
use Proovit\UrlWatcher\Support\DefaultUrlWatchFingerprintResolver;
use Proovit\UrlWatcher\Support\DefaultUrlWatchNotifier;
use Proovit\UrlWatcher\Support\DefaultUrlWatchRepository;
use Proovit\UrlWatcher\Support\UrlWatchCatalog;

final class UrlWatcherServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/url-watcher.php', 'url-watcher');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'laravel-url-watcher');

        $this->app->singleton(UrlWatchCatalog::class, static function (): UrlWatchCatalog {
            return UrlWatchCatalog::defaults();
        });

        $this->app->singleton(UrlWatchFingerprintResolverInterface::class, DefaultUrlWatchFingerprintResolver::class);
        $this->app->singleton(UrlWatchClassifierInterface::class, DefaultUrlWatchClassifier::class);
        $this->app->singleton(UrlWatchCapturePolicyInterface::class, DefaultUrlWatchCapturePolicy::class);
        $this->app->singleton(UrlWatcherSettingsRepositoryInterface::class, DefaultUrlWatcherSettingsRepository::class);
        $this->app->singleton(UrlWatchRepositoryInterface::class, DefaultUrlWatchRepository::class);
        $this->app->singleton(UrlWatchNotifierInterface::class, DefaultUrlWatchNotifier::class);
        $this->app->singleton(UrlWatcher::class);
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                SendUrlWatchDigestCommand::class,
                PruneUrlWatchHistoryCommand::class,
            ]);

            $this->publishes([
                __DIR__.'/../config/url-watcher.php' => config_path('url-watcher.php'),
            ], 'url-watcher-config');

            $this->publishes([
                __DIR__.'/../database/migrations' => database_path('migrations'),
            ], 'url-watcher-migrations');
        }

        if ((bool) config('url-watcher.enabled', true)) {
            $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        }
    }
}
