<?php

use Illuminate\Support\Facades\Route;
use Chrisquices\Sentinel\Http\Controllers\IndexController;
use Chrisquices\Sentinel\Http\Controllers\LogController;
use Chrisquices\Sentinel\Http\Controllers\QueueController;
use Chrisquices\Sentinel\Http\Controllers\RuntimeController;
use Chrisquices\Sentinel\Http\Controllers\SchedulerController;
use Chrisquices\Sentinel\Http\Controllers\SystemController;

Route::group([
    'prefix' => config('sentinel.path', 'sentinel'),
    'middleware' => config('sentinel.middleware', ['web']),
    'as' => 'sentinel.',
], function () {
    Route::get('/', IndexController::class)->name('index');

    Route::prefix('api')->name('api.')->group(function () {

        // System
        Route::prefix('system')->name('system.')->group(function () {
            Route::get('/', [SystemController::class, 'show'])->name('show');
        });

        // Runtime
        Route::prefix('runtime')->name('runtime.')->group(function () {
            Route::get('/', [RuntimeController::class, 'show'])->name('show');
        });

        // Scheduler
        Route::prefix('scheduler')->name('scheduler.')->group(function () {
            Route::get('/', [SchedulerController::class, 'show'])->name('show');
        });

        // Queue
        Route::prefix('queue')->name('queue.')->group(function () {
            Route::get('/', [QueueController::class, 'show'])->name('show');

            // Completed Jobs
            Route::prefix('completed')->name('completed.')->group(function () {
                Route::delete('/clear', [QueueController::class, 'clearCompletedJobs'])->name('clear');
                Route::delete('/{id}', [QueueController::class, 'deleteCompletedJob'])->name('delete');
            });

            // Failed Jobs
            Route::prefix('failed')->name('failed.')->group(function () {
                Route::delete('/clear', [QueueController::class, 'clearFailedJobs'])->name('clear');
                Route::post('/{id}/retry', [QueueController::class, 'retryFailedJob'])->name('retry');
                Route::delete('/{id}', [QueueController::class, 'deleteFailedJob'])->name('delete');
            });
        });

        // Logs
        Route::prefix('logs')->name('logs.')->group(function () {
            Route::get('/channels', [LogController::class, 'channels'])->name('channels');
            Route::get('/{channel}/entries', [LogController::class, 'entries'])->name('entries');
            Route::get('/{channel}/tail', [LogController::class, 'tail'])->name('tail');
            Route::delete('/{channel}/clear', [LogController::class, 'clear'])->name('clear');
        });
    });
});
