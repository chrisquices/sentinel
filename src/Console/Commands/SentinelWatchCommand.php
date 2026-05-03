<?php

namespace Chrisquices\VulcanSentinel\Console\Commands;

use Illuminate\Console\Command;

class SentinelWatchCommand extends Command
{
    protected $signature = 'vulcan-sentinel:watch {--interval=5 : Polling interval in seconds}';

    protected $description = 'Watch and report on application health metrics in real time';

    public function handle(): int
    {
        return self::SUCCESS;
    }
}
