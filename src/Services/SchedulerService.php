<?php

namespace Chrisquices\Sentinel\Services;

use Chrisquices\Sentinel\Helpers\SchedulerHelper;
use Illuminate\Console\Events\ScheduledTaskFinished;
use Illuminate\Console\Scheduling\CallbackEvent;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\DB;

class SchedulerService
{
    public static function get(): array
    {
        $events = self::getEvents();


        return [
            'events' => $events
        ];
    }

    public static function getEvents(): array
    {
        try {
            $kernel = app(\Illuminate\Contracts\Console\Kernel::class);
            $kernel->bootstrap();
            $schedule = app(Schedule::class);

        } catch (\Throwable) {
            return [];
        }

        $events = [];

        foreach ($schedule->events() as $event) {
            $command = self::resolveCommand($event);

            $lastRun = DB::table('sentinel_scheduler_runs')
                ->where('command', $command)
                ->orderByDesc('ran_at')
                ->first();

            $nextRun = $event->nextRunDate();

            $events[] = [
                'command'         => $command,
                'expression'      => $event->expression,
                'expressionLabel' => SchedulerHelper::humanCron($event->expression),
                'nextRun'         => $nextRun->toIso8601String(),
                'lastRanAt'       => $lastRun?->ran_at,
                'exitCode'        => $lastRun?->exit_code,
                'status'          => self::resolveStatus($lastRun),
            ];
        }

        return $events;
    }

    public static function recordRun(ScheduledTaskFinished $event): void
    {
        DB::table('sentinel_scheduler_runs')->insert([
            'command'   => self::resolveCommand($event->task),
            'ran_at'    => now(),
            'exit_code' => $event->task->exitCode,
        ]);
    }

    // region --- Helpers --------------------------------------------------------------------------------------------------

    private static function resolveCommand(Event $event): string
    {
        if ($event instanceof CallbackEvent) {
            return $event->description ?: '{Closure}';
        }

        $cmd = $event->command ?? '';

        if (preg_match('/artisan\s+(.+)/', $cmd, $m)) {
            return 'artisan ' . trim($m[1]);
        }

        return trim($cmd) ?: '{Command}';
    }

    private static function resolveStatus(?object $lastRun): string
    {
        if (!$lastRun) return 'never';
        return $lastRun->exit_code === 0 || $lastRun->exit_code === null ? 'success' : 'failed';
    }

    // endregion
}
