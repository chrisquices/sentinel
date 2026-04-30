<?php

namespace Chrisquices\VulcanSentinel;

use Illuminate\Support\ServiceProvider;
use Chrisquices\VulcanSentinel\Console\Commands\SentinelWatchCommand;

class VulcanSentinelServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/sentinel.php',
            'sentinel'
        );
    }

    public function boot(): void
    {
        $this->registerPublishing();
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'sentinel');
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        if ($this->app->runningInConsole()) {
            $this->commands([
                SentinelWatchCommand::class,
            ]);
        }
    }

    protected function registerPublishing(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__.'/../config/sentinel.php' => config_path('sentinel.php'),
        ], 'sentinel-config');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/sentinel'),
        ], 'sentinel-views');

        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'sentinel-migrations');

        $this->publishes([
            __DIR__.'/../public' => public_path('vendor/vulcan-sentinel'),
        ], 'sentinel-assets');
    }
}
