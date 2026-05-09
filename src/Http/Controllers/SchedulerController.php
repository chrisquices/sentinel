<?php

namespace Chrisquices\VulcanSentinel\Http\Controllers;

use Chrisquices\VulcanSentinel\Services\SchedulerService;
use Illuminate\Routing\Controller;

class SchedulerController extends Controller
{
    public function show()
    {
        $schedulerData = SchedulerService::getEvents();

        return response()->json($schedulerData);
    }
}
