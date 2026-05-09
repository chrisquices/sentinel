<?php

namespace Chrisquices\Sentinel;

use Illuminate\Support\ServiceProvider;
use Chrisquices\Sentinel\Console\Commands\SentinelWatchCommand;
use Illuminate\Console\Events\ScheduledTaskFinished;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Support\Facades\Event;

class SentinelServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/sentinel.php',
            'sentinel'
        );
    }

    public function boot(): void
    {
        $this->registerPublishing();
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'sentinel');
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        if ($this->app->runningInConsole()) {
            $this->commands([
                SentinelWatchCommand::class,
            ]);
        }

        Event::listen(JobProcessed::class, function (JobProcessed $event) {
            \Chrisquices\Sentinel\Services\QueueService::recordCompletedJob($event);
        });

        Event::listen(ScheduledTaskFinished::class, function (ScheduledTaskFinished $event) {
            \Chrisquices\Sentinel\Services\SchedulerService::recordRun($event);
        });
    }

    protected function registerPublishing(): void
    {
        if (!$this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__ . '/../config/sentinel.php' => config_path('sentinel.php'),
        ], 'sentinel-config');

        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/sentinel'),
        ], 'sentinel-views');

        $this->publishes([
            __DIR__ . '/../database/migrations' => database_path('migrations'),
        ], 'sentinel-migrations');

        $this->publishes([
            __DIR__ . '/../dist' => public_path('vendor/sentinel'),
        ], 'sentinel-assets');
    }
}
