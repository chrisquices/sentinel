<?php

namespace Chrisquices\Sentinel;

use Illuminate\Support\ServiceProvider;
use Chrisquices\Sentinel\Http\Middleware\SentinelAuth;
use Illuminate\Console\Events\ScheduledTaskFinished;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Support\Facades\Event;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

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
        $this->registerRoutes();
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        Gate::define('viewSentinel', function ($user) {
            return app()->environment('local');
        });

        RateLimiter::for('sentinel-mutations', function ($request) {
            return Limit::perMinute(10)->by($request->ip())->response(function ($request, array $headers) {
                return response()->json(['error' => 'Too many requests.'], 429, $headers);
            });
        });

        Event::listen(JobProcessed::class, function (JobProcessed $event) {
            \Chrisquices\Sentinel\Services\QueueService::recordCompletedJob($event);
        });

        Event::listen(ScheduledTaskFinished::class, function (ScheduledTaskFinished $event) {
            \Chrisquices\Sentinel\Services\SchedulerService::recordRun($event);
        });
    }

    protected function registerRoutes(): void
    {
        Route::group([
            'prefix' => config('sentinel.path', 'sentinel'),
            'middleware' => array_merge(config('sentinel.middleware', ['web']), [SentinelAuth::class]),
            'as' => 'sentinel.',
        ], function () {
            $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
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
