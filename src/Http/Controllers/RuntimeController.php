<?php

namespace Chrisquices\VulcanSentinel\Http\Controllers;

use Chrisquices\VulcanSentinel\Services\RuntimeService;
use Illuminate\Routing\Controller;

class RuntimeController extends Controller
{
    public function show()
    {
        $runtimeData = RuntimeService::get();
        
        return response()->json($runtimeData);
    }
}
