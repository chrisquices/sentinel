<?php

namespace Chrisquices\VulcanSentinel\Http\Controllers;

use Chrisquices\VulcanSentinel\Services\QueueService;
use Illuminate\Routing\Controller;

class SchedulerController extends Controller
{
    public function show()
    {
        return response()->json(QueueService::get());
    }
}
