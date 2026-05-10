<?php

namespace Chrisquices\Sentinel\Http\Controllers;

use Illuminate\Routing\Controller;

class IndexController extends Controller
{
    public function __invoke()
    {
        return view('sentinel::app');
    }
}
