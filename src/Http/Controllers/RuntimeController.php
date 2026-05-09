<?php

namespace Chrisquices\Sentinel\Http\Controllers;

use Chrisquices\Sentinel\Services\RuntimeService;
use Illuminate\Routing\Controller;

class RuntimeController extends Controller
{
    public function show()
    {
        $runtimeData = RuntimeService::get();
        
        return response()->json($runtimeData);
    }
}
