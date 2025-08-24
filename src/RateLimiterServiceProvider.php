<?php

namespace Deecodek\RateLimiter;

use Illuminate\Support\ServiceProvider;
use Deecodek\RateLimiter\Commands\ClearRateLimitsCommand;
use Deecodek\RateLimiter\Commands\RateLimitStatsCommand;
use Deecodek\RateLimiter\Commands\BlockIpCommand;
use Deecodek\RateLimiter\Commands\UnblockIpCommand;
use Deecodek\RateLimiter\Commands\ResetQuotaCommand;
use Deecodek\RateLimiter\Commands\InstallCommand;
use Deecodek\RateLimiter\Commands\PublishCommand;
use Deecodek\RateLimiter\Commands\AnalyzeCommand;
use Deecodek\RateLimiter\Commands\ExportCommand;
use Deecodek\RateLimiter\Commands\ImportRulesCommand;
use Deecodek\RateLimiter\Middleware\RateLimitMiddleware;

class RateLimiterServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Merge config
        $this->mergeConfigFrom(
            __DIR__.'/../config/rate-limiter.php', 
            'rate-limiter'
        );

        // Register services
        $this->app->singleton('rate-limiter', function ($app) {
            return new Services\RateLimiterService($app);
        });
        
        $this->app->singleton('rate-limiter.analytics', function ($app) {
            return new Services\AnalyticsService($app);
        });
        
        $this->app->singleton('rate-limiter.alerts', function ($app) {
            return new Services\AlertService($app);
        });
    }

    public function boot()
    {
        // Publish config
        $this->publishes([
            __DIR__.'/../config/rate-limiter.php' => config_path('rate-limiter.php'),
        ], 'rate-limiter-config');

        // Publish migrations
        $this->publishes([
            __DIR__.'/../database/migrations/' => database_path('migrations'),
        ], 'rate-limiter-migrations');

        // Load migrations
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // Load routes for webhooks
        $this->loadRoutesFrom(__DIR__.'/../routes/channels.php');

        // Register middleware
        $this->app['router']->aliasMiddleware('rate-limit', RateLimitMiddleware::class);

        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                ClearRateLimitsCommand::class,
                RateLimitStatsCommand::class,
                BlockIpCommand::class,
                UnblockIpCommand::class,
                ResetQuotaCommand::class,
                InstallCommand::class,
                PublishCommand::class,
                AnalyzeCommand::class,
                ExportCommand::class,
                ImportRulesCommand::class,
            ]);
        }
    }
}