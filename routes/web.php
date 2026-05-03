<?php

use Illuminate\Support\Facades\Route;
use Chrisquices\VulcanSentinel\Http\Controllers\SentinelController;

Route::group([
    'prefix' => config('vulcan-sentinel.path', 'vulcan-sentinel'),
    'middleware' => config('vulcan-sentinel.middleware', ['web']),
    'as' => 'vulcan-sentinel.',
], function () {
    Route::get('/', [SentinelController::class, 'index'])->name('index');

    Route::prefix('api')->name('api.')->group(function () {

        // System
        Route::prefix('system')->name('system.')->group(function () {
            Route::get('/', [SentinelController::class, 'system'])->name('index');
        });

        // Queue
        Route::prefix('queue')->name('queue.')->group(function () {
            Route::get('/', [SentinelController::class, 'queue'])->name('index');

            // Completed Jobs
            Route::prefix('completed')->name('completed.')->group(function () {
                Route::delete('/clear', [SentinelController::class, 'clearCompletedJobs'])->name('clear');
                Route::delete('/{id}', [SentinelController::class, 'deleteCompletedJob'])->name('delete');
            });

            // Failed Jobs
            Route::prefix('failed')->name('failed.')->group(function () {
                Route::delete('/clear', [SentinelController::class, 'clearFailedJobs'])->name('clear');
                Route::post('/{id}/retry', [SentinelController::class, 'retryFailedJob'])->name('retry');
                Route::delete('/{id}', [SentinelController::class, 'deleteFailedJob'])->name('delete');
            });
        });
    });
});
