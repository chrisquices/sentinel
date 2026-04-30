<?php

namespace Chrisquices\VulcanSentinel\Http\Controllers;

use Illuminate\Routing\Controller;

class SentinelController extends Controller
{
    public function index()
    {
        return view('sentinel::app');
    }

    public function system()
    {
        //
    }

    public function jobs()
    {
        //
    }

    public function retryJob(string $id)
    {
        //
    }

    public function scheduler()
    {
        //
    }

    public function logs()
    {
        //
    }

    public function deleteLogs()
    {
        //
    }
}
