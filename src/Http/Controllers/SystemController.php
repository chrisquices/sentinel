<?php

namespace Chrisquices\VulcanSentinel\Http\Controllers;

use Chrisquices\VulcanSentinel\Services\SystemService;
use Illuminate\Routing\Controller;

class SystemController extends Controller
{
    public function show()
    {
        $systemData = SystemService::get();

        return response()->json($systemData);
    }
}
