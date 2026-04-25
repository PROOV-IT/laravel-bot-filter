<?php

declare(strict_types=1);

namespace Proovit\BotFilter;

use Illuminate\Support\ServiceProvider;
use Proovit\BotFilter\Contracts\BotFilterSettingsRepositoryInterface;
use Proovit\BotFilter\Contracts\BotProbeCapturePolicyInterface;
use Proovit\BotFilter\Contracts\BotProbeClassifierInterface;
use Proovit\BotFilter\Contracts\BotProbeFingerprintResolverInterface;
use Proovit\BotFilter\Contracts\BotProbeNotifierInterface;
use Proovit\BotFilter\Contracts\BotProbeRepositoryInterface;
use Proovit\BotFilter\Support\BotProbeCatalog;
use Proovit\BotFilter\Support\DefaultBotFilterSettingsRepository;
use Proovit\BotFilter\Support\DefaultBotProbeCapturePolicy;
use Proovit\BotFilter\Support\DefaultBotProbeClassifier;
use Proovit\BotFilter\Support\DefaultBotProbeFingerprintResolver;
use Proovit\BotFilter\Support\DefaultBotProbeNotifier;
use Proovit\BotFilter\Support\DefaultBotProbeRepository;
use Proovit\BotFilter\Console\Commands\SendBotProbeDigestCommand;

final class BotFilterServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/bot-filter.php', 'bot-filter');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'laravel-bot-filter');

        $this->app->singleton(BotProbeCatalog::class, static function (): BotProbeCatalog {
            return BotProbeCatalog::defaults();
        });

        $this->app->singleton(BotProbeFingerprintResolverInterface::class, DefaultBotProbeFingerprintResolver::class);
        $this->app->singleton(BotProbeClassifierInterface::class, DefaultBotProbeClassifier::class);
        $this->app->singleton(BotProbeCapturePolicyInterface::class, DefaultBotProbeCapturePolicy::class);
        $this->app->singleton(BotFilterSettingsRepositoryInterface::class, DefaultBotFilterSettingsRepository::class);
        $this->app->singleton(BotProbeRepositoryInterface::class, DefaultBotProbeRepository::class);
        $this->app->singleton(BotProbeNotifierInterface::class, DefaultBotProbeNotifier::class);
        $this->app->singleton(BotFilter::class);
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                SendBotProbeDigestCommand::class,
            ]);

            $this->publishes([
                __DIR__.'/../config/bot-filter.php' => config_path('bot-filter.php'),
            ], 'bot-filter-config');

            $this->publishes([
                __DIR__.'/../database/migrations' => database_path('migrations'),
            ], 'bot-filter-migrations');
        }

        if ((bool) config('bot-filter.enabled', true)) {
            $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        }
    }
}
