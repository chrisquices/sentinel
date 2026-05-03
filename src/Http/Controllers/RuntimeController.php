<?php

namespace Chrisquices\VulcanSentinel\Http\Controllers;

use Chrisquices\VulcanSentinel\Services\RuntimeService;
use Illuminate\Routing\Controller;

class RuntimeController extends Controller
{
    public function show()
    {
        return response()->json(RuntimeService::getInfo());
    }
}
