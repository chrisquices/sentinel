<?php

namespace Chrisquices\Sentinel\Http\Controllers;

use Chrisquices\Sentinel\Services\SchedulerService;
use Illuminate\Routing\Controller;

class SchedulerController extends Controller
{
    public function show()
    {
        return response()->json(SchedulerService::get());
    }
}
