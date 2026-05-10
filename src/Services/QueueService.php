<?php

namespace Chrisquices\Sentinel\Services;

use Chrisquices\Sentinel\Helpers\QueueHelper;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Artisan;

class QueueService
{
    private const SUPPORTED_DRIVERS = ['database', 'redis'];

    public static function get(): array
    {
        $driver = config('queue.default');

        if (! in_array($driver, self::SUPPORTED_DRIVERS)) {
            return [
                'unsupportedDriver' => true,
                'driver'            => $driver,
                'pollInterval'      => (int) config('sentinel.poll_interval', 3),
            ];
        }

        return [
            'summary'      => self::getSummary(),
            'jobs'         => self::getJobs(),
            'pollInterval' => (int) config('sentinel.poll_interval', 3),
        ];
    }

    // region --- Summary ----------------------------------------------------------------------------------------------
    public static function getSummary(): array
    {
        return [
            'pending' => self::getPendingCount(),
            'processing' => self::getProcessingCount(),
            'completed' => self::getCompletedCount(),
            'failed' => self::getFailedCount(),
        ];
    }

    public static function getPendingCount(): int
    {
        $driver = config('queue.default');

        if ($driver === 'redis') {
            $connection = config('queue.connections.redis.queue', 'default');
            return (int)Redis::llen("queues:{$connection}");
        }

        return DB::table('jobs')->whereNull('reserved_at')->count();
    }

    public static function getProcessingCount(): int
    {
        $driver = config('queue.default');

        if ($driver === 'redis') {
            $connection = config('queue.connections.redis.queue', 'default');
            return (int)Redis::llen("queues:{$connection}:reserved");
        }

        return DB::table('jobs')->whereNotNull('reserved_at')->count();
    }

    public static function getCompletedCount(): int
    {
        return DB::table('sentinel_completed_jobs')->count();
    }

    public static function getFailedCount(): int
    {
        return DB::table('failed_jobs')->count();
    }

    // endregion

    // region --- Jobs -------------------------------------------------------------------------------------------------
    public static function getJobs(): array
    {
        $driver = config('queue.default');
        $jobs = [];

        // Pending & Processing
        if ($driver === 'redis') {
            $queue = config('queue.connections.redis.queue', 'default');

            $pending = Redis::lrange("queues:{$queue}", 0, -1) ?: [];
            foreach ($pending as $raw) {
                $payload = json_decode($raw, true);
                $jobs[] = [
                    'id' => $payload['uuid'] ?? null,
                    'jobClass' => $payload['data']['commandName'] ?? $payload['job'] ?? 'Unknown',
                    'displayName' => $payload['displayName'] ?? 'Unknown',
                    'queue' => $queue,
                    'status' => 'pending',
                    'attempts' => $payload['attempts'] ?? 0,
                    'createdAt' => null,
                    'createdAtFormatted' => '—',
                ];
            }

            $reserved = Redis::lrange("queues:{$queue}:reserved", 0, -1) ?: [];
            foreach ($reserved as $raw) {
                $payload = json_decode($raw, true);
                $jobs[] = [
                    'id' => $payload['uuid'] ?? null,
                    'jobClass' => $payload['data']['commandName'] ?? $payload['job'] ?? 'Unknown',
                    'displayName' => $payload['displayName'] ?? 'Unknown',
                    'queue' => $queue,
                    'status' => 'processing',
                    'attempts' => $payload['attempts'] ?? 0,
                    'createdAt' => null,
                    'createdAtFormatted' => '—',
                ];
            }
        } else {
            $pending = DB::table('jobs')->orderByDesc('created_at')->get();
            foreach ($pending as $job) {
                $payload = QueueHelper::decodePayload($job->payload);
                $jobs[] = [
                    'id' => $job->uuid ?? $job->id,
                    'jobClass' => $payload['class'],
                    'displayName' => $payload['displayName'],
                    'queue' => $job->queue,
                    'status' => is_null($job->reserved_at) ? 'pending' : 'processing',
                    'attempts' => $job->attempts,
                    'createdAt' => $job->created_at,
                    'createdAtFormatted' => QueueHelper::formatDateTime(date('Y-m-d H:i:s', $job->created_at)),
                ];
            }
        }

        // Completed
        $completed = DB::table('sentinel_completed_jobs')->orderByDesc('completed_at')->get();
        foreach ($completed as $job) {
            $jobs[] = [
                'id' => $job->id,
                'jobClass' => $job->job_class,
                'displayName' => $job->display_name,
                'queue' => $job->queue,
                'status' => 'completed',
                'attempts' => $job->attempts,
                'createdAt' => $job->completed_at,
                'createdAtFormatted' => QueueHelper::formatDateTime($job->completed_at),
                'runTime' => $job->run_time,
                'runTimeFormatted' => $job->run_time ? $job->run_time . 'ms' : '—',
            ];
        }

        // Failed
        $failed = DB::table('failed_jobs')->orderByDesc('failed_at')->get();
        foreach ($failed as $job) {
            $payload = QueueHelper::decodePayload($job->payload);
            $jobs[] = [
                'id' => $job->uuid ?? $job->id,
                'jobClass' => $payload['class'],
                'displayName' => $payload['displayName'],
                'queue' => $job->queue,
                'status' => 'failed',
                'attempts' => $payload['attempts'],
                'createdAt' => $job->failed_at,
                'createdAtFormatted' => QueueHelper::formatDateTime($job->failed_at),
                'exception' => QueueHelper::truncateException($job->exception),
                'exceptionFull' => $job->exception,
            ];
        }

        return $jobs;
    }

    public static function getJobPayload(string $id): ?array
    {
        // Pending / processing (jobs table, keyed by uuid)
        $job = DB::table('jobs')->where('uuid', $id)->first();
        if ($job) {
            $raw = json_decode($job->payload, true) ?? [];
            unset($raw['data']['command']);
            return [
                'source'      => is_null($job->reserved_at) ? 'pending' : 'processing',
                'jobClass'    => $raw['data']['commandName'] ?? $raw['job'] ?? 'Unknown',
                'displayName' => $raw['displayName'] ?? 'Unknown',
                'queue'       => $job->queue,
                'connection'  => null,
                'attempts'    => $job->attempts,
                'payload'     => $raw,
            ];
        }

        // Failed jobs (failed_jobs table, keyed by uuid)
        $failed = DB::table('failed_jobs')->where('uuid', $id)->first();
        if ($failed) {
            $raw = json_decode($failed->payload, true) ?? [];
            unset($raw['data']['command']);
            return [
                'source'      => 'failed',
                'jobClass'    => $raw['data']['commandName'] ?? $raw['job'] ?? 'Unknown',
                'displayName' => $raw['displayName'] ?? 'Unknown',
                'queue'       => $failed->queue,
                'connection'  => $failed->connection,
                'attempts'    => $raw['attempts'] ?? 0,
                'failedAt'    => $failed->failed_at,
                'exception'   => $failed->exception,
                'payload'     => $raw,
            ];
        }

        // Completed jobs (sentinel_completed_jobs, integer id or uuid)
        $completed = DB::table('sentinel_completed_jobs')
            ->where(function ($q) use ($id) {
                $q->where('uuid', $id);
                if (is_numeric($id)) {
                    $q->orWhere('id', (int) $id);
                }
            })
            ->first();
        if ($completed) {
            return [
                'source'      => 'completed',
                'jobClass'    => $completed->job_class,
                'displayName' => $completed->display_name,
                'queue'       => $completed->queue,
                'connection'  => $completed->connection,
                'attempts'    => $completed->attempts,
                'runTime'     => $completed->run_time,
                'completedAt' => $completed->completed_at,
                'payload'     => null,
            ];
        }

        return null;
    }

    public static function recordCompletedJob(object $event): void
    {
        $job = $event->job;
        $payload = $job->payload();

        DB::table('sentinel_completed_jobs')->insert([
            'uuid'         => $payload['uuid'] ?? null,
            'connection'   => $job->getConnectionName(),
            'queue'        => $job->getQueue(),
            'job_class'    => $payload['data']['commandName'] ?? $payload['job'] ?? 'Unknown',
            'display_name' => $payload['displayName'] ?? 'Unknown',
            'attempts'     => $job->attempts(),
            'run_time'     => isset($payload['pushedAt']) ? (int)((microtime(true) - $payload['pushedAt']) * 1000) : null,
            'completed_at' => now(),
        ]);
    }

    // endregion

    // region --- Completed Jobs ---------------------------------------------------------------------------------------
    public static function deleteCompletedJob(string $id): bool
    {
        try {
            DB::table('sentinel_completed_jobs')->where('id', $id)->delete();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public static function clearCompletedJobs(): bool
    {
        try {
            DB::table('sentinel_completed_jobs')->truncate();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    // endregion

    // region --- Failed Jobs ------------------------------------------------------------------------------------------
    public static function retryFailedJob(string $id): bool
    {
        try {
            Artisan::call('queue:retry', ['id' => [$id]]);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public static function deleteFailedJob(string $id): bool
    {
        try {
            Artisan::call('queue:forget', ['id' => [$id]]);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public static function clearFailedJobs(): bool
    {
        try {
            Artisan::call('queue:flush');
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
    // endregion
}