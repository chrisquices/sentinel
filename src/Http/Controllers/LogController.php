<?php

namespace Chrisquices\VulcanSentinel\Http\Controllers;

use Chrisquices\VulcanSentinel\Services\LogService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class LogController extends Controller
{
    public function channels()
    {
        return response()->json(LogService::getChannels());
    }

    public function entries(Request $request, string $channel)
    {
        $page    = (int) $request->query('page', 1);
        $perPage = (int) $request->query('perPage', 25);
        $level   = $request->query('level') ?: null;

        return response()->json(LogService::getLogs($channel, $page, $perPage, $level));
    }

    public function tail(Request $request, string $channel)
    {
        $tailCursor = (int) $request->query('tailCursor', 0);
        $level      = $request->query('level') ?: null;

        return response()->json(LogService::getLogTail($channel, $tailCursor, $level));
    }

    public function clear(string $channel)
    {
        LogService::clearLog($channel);

        return response()->noContent();
    }
}