<?php

namespace Chrisquices\Sentinel\Http\Controllers;

use Chrisquices\Sentinel\Services\SystemService;
use Illuminate\Routing\Controller;

class IndexController extends Controller
{
    public function __invoke()
    {
        SystemService::forgetCpuHistory();

        return view('sentinel::app');
    }
}
