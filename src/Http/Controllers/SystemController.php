<?php

namespace Chrisquices\VulcanSentinel\Http\Controllers;

use Chrisquices\VulcanSentinel\Services\LogService;
use Chrisquices\VulcanSentinel\Services\QueueService;
use Chrisquices\VulcanSentinel\Services\SchedulerService;
use Chrisquices\VulcanSentinel\Services\SystemService;
use Illuminate\Routing\Controller;

class SystemController extends Controller
{
    public function index()
    {
        SystemService::forgetCpuHistory();

        return view('vulcan-sentinel::app', [
            'system'    => SystemService::get(),
            'queue'     => QueueService::get(),
            'scheduler' => SchedulerService::getEvents(),
            'channels'  => LogService::getChannels(),
        ]);
    }

    public function show()
    {
        return response()->json(SystemService::get());
    }
}
