<?php

namespace Chrisquices\VulcanSentinel\Http\Controllers;

use Chrisquices\VulcanSentinel\Services\LogService;
use Chrisquices\VulcanSentinel\Services\QueueService;
use Chrisquices\VulcanSentinel\Services\SchedulerService;
use Chrisquices\VulcanSentinel\Services\SystemService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class IndexController extends Controller
{
    public function __invoke(Request $request)
    {
        SystemService::forgetCpuHistory();

        $systemData = SystemService::get();
        $queueData = QueueService::get();
        $schedulerData = SchedulerService::get();
        $logsData = LogService::get();

        return view('vulcan-sentinel::app', [
            'systemData'         => $systemData,
            'queueData'          => $queueData,
            'schedulerData'      => $schedulerData,
            'logsData'           => $logsData,
        ]);
    }
}
