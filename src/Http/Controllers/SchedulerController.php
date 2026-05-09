<?php

namespace Chrisquices\Sentinel\Http\Controllers;

use Chrisquices\Sentinel\Services\SchedulerService;
use Illuminate\Routing\Controller;

class SchedulerController extends Controller
{
    public function show()
    {
        $schedulerData = SchedulerService::getEvents();

        return response()->json($schedulerData);
    }
}
