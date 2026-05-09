<?php

namespace Chrisquices\Sentinel\Http\Controllers;

use Chrisquices\Sentinel\Services\SystemService;
use Illuminate\Routing\Controller;

class SystemController extends Controller
{
    public function show()
    {
        $systemData = SystemService::get();

        return response()->json($systemData);
    }
}
