<?php

use Illuminate\Support\Facades\Route;
use Chrisquices\VulcanSentinel\Http\Controllers\SentinelController;

Route::group([
    'prefix' => config('sentinel.path', 'sentinel'),
    'middleware' => config('sentinel.middleware', ['web']),
    'as' => 'sentinel.',
], function () {
    Route::get('/', [SentinelController::class, 'index'])->name('index');

    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/system', [SentinelController::class, 'system'])->name('system');
        Route::get('/jobs', [SentinelController::class, 'jobs'])->name('jobs');
        Route::post('/jobs/{id}/retry', [SentinelController::class, 'retryJob'])->name('jobs.retry');
        Route::get('/scheduler', [SentinelController::class, 'scheduler'])->name('scheduler');
        Route::get('/logs', [SentinelController::class, 'logs'])->name('logs');
        Route::delete('/logs', [SentinelController::class, 'deleteLogs'])->name('logs.delete');
    });
});
