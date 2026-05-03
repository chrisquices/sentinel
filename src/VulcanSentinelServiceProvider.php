<?php

namespace Chrisquices\VulcanSentinel;

use Illuminate\Support\ServiceProvider;
use Chrisquices\VulcanSentinel\Console\Commands\SentinelWatchCommand;
use Illuminate\Console\Events\ScheduledTaskFinished;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Support\Facades\Event;

class VulcanSentinelServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/vulcan-sentinel.php',
            'vulcan-sentinel'
        );
    }

    public function boot(): void
    {
        $this->registerPublishing();
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'vulcan-sentinel');
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        if ($this->app->runningInConsole()) {
            $this->commands([
                SentinelWatchCommand::class,
            ]);
        }

        Event::listen(JobProcessed::class, function (JobProcessed $event) {
            \Chrisquices\VulcanSentinel\Services\QueueService::recordCompletedJob($event);
        });

        Event::listen(ScheduledTaskFinished::class, function (ScheduledTaskFinished $event) {
            \Chrisquices\VulcanSentinel\Services\SchedulerService::recordRun($event);
        });
    }

    protected function registerPublishing(): void
    {
        if (!$this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__ . '/../config/vulcan-sentinel.php' => config_path('vulcan-sentinel.php'),
        ], 'vulcan-sentinel-config');

        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/vulcan-sentinel'),
        ], 'vulcan-sentinel-views');

        $this->publishes([
            __DIR__ . '/../database/migrations' => database_path('migrations'),
        ], 'vulcan-sentinel-migrations');

        $this->publishes([
            __DIR__ . '/../dist' => public_path('vendor/vulcan-sentinel'),
        ], 'vulcan-sentinel-assets');
    }
}
