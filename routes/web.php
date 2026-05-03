<?php

use Illuminate\Support\Facades\Route;
use Chrisquices\VulcanSentinel\Http\Controllers\QueueController;
use Chrisquices\VulcanSentinel\Http\Controllers\RuntimeController;
use Chrisquices\VulcanSentinel\Http\Controllers\SchedulerController;
use Chrisquices\VulcanSentinel\Http\Controllers\SystemController;

Route::group([
    'prefix' => config('vulcan-sentinel.path', 'vulcan-sentinel'),
    'middleware' => config('vulcan-sentinel.middleware', ['web']),
    'as' => 'vulcan-sentinel.',
], function () {
    Route::get('/', [SystemController::class, 'index'])->name('index');

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
    });
});
