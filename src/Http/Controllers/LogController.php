<?php

namespace Chrisquices\Sentinel\Http\Controllers;

use Chrisquices\Sentinel\Services\LogService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;

class LogController extends Controller
{
    public function index()
    {
        return response()->json(LogService::get());
    }

    public function channels()
    {
        return response()->json(LogService::getChannels());
    }

    public function entries(Request $request, string $channel)
    {
        if (! $this->validChannel($channel)) {
            return response()->json(['error' => 'Invalid channel name.'], 400);
        }

        $v = Validator::make($request->query(), [
            'page'   => 'nullable|integer|min:1',
            'level'  => 'nullable|string|in:error,warning,info,debug',
            'search' => 'nullable|string|max:255',
        ]);

        if ($v->fails()) {
            return response()->json(['errors' => $v->errors()], 422);
        }

        $page   = (int) $request->query('page', 1);
        $level  = $request->query('level') ?: null;
        $search = $request->query('search') ?: null;

        return response()->json(LogService::getLogs($channel, $page, $level, $search));
    }

    public function tail(Request $request, string $channel)
    {
        if (! $this->validChannel($channel)) {
            return response()->json(['error' => 'Invalid channel name.'], 400);
        }

        $v = Validator::make($request->query(), [
            'tailCursor' => 'nullable|integer|min:0',
            'level'      => 'nullable|string|in:error,warning,info,debug',
        ]);

        if ($v->fails()) {
            return response()->json(['errors' => $v->errors()], 422);
        }

        $tailCursor = (int) $request->query('tailCursor', 0);
        $level      = $request->query('level') ?: null;

        return response()->json(LogService::getLogTail($channel, $tailCursor, $level));
    }

    public function clear(string $channel)
    {
        if (! $this->validChannel($channel)) {
            return response()->json(['error' => 'Invalid channel name.'], 400);
        }

        LogService::clearLog($channel);

        return response()->noContent();
    }

    private function validChannel(string $channel): bool
    {
        return (bool) preg_match('/^[a-zA-Z0-9_-]+$/', $channel);
    }
}