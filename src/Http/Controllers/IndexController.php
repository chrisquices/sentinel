<?php

namespace Chrisquices\Sentinel\Http\Controllers;

use Chrisquices\Sentinel\Services\LogService;
use Chrisquices\Sentinel\Services\QueueService;
use Chrisquices\Sentinel\Services\RuntimeService;
use Chrisquices\Sentinel\Services\SchedulerService;
use Chrisquices\Sentinel\Services\SystemService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class IndexController extends Controller
{
    public function __invoke(Request $request)
    {
        SystemService::forgetCpuHistory();

        $systemData = SystemService::get();
        $runtimeData = RuntimeService::get();
        $queueData = QueueService::get();
        $schedulerData = SchedulerService::get();
        $logsData = LogService::get();

        return view('sentinel::app', [
            'systemData' => $systemData,
            'runtimeData' => $runtimeData,
            'queueData' => $queueData,
            'schedulerData' => $schedulerData,
            'logsData' => $logsData,
        ]);
    }
}
