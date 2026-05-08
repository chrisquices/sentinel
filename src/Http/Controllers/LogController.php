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

    public function entries(Request $request)
    {
        $channel     = $request->query('channel', '');
        $level       = $request->query('level', '');
        $tailCursor  = $request->query('tail_cursor');
        $cursor      = $request->query('cursor');

        $channelData = $this->resolveChannel($channel);
        if (!$channelData) {
            return response()->json(['error' => 'Channel not found'], 404);
        }

        if ($tailCursor !== null) {
            return response()->json(LogService::getTailEntries($channelData['path'], (int) $tailCursor, $level));
        }

        return response()->json(LogService::getEntries(
            $channelData['path'],
            $cursor !== null ? (int) $cursor : null,
            20,
            $level,
        ));
    }

    public function clear(Request $request)
    {
        $channel     = $request->query('channel', '');
        $channelData = $this->resolveChannel($channel);

        if (!$channelData) {
            return response()->json(['error' => 'Channel not found'], 404);
        }

        return response()->json(['success' => LogService::clearLog($channelData['path'])]);
    }

    private function resolveChannel(string $name): ?array
    {
        return collect(LogService::getChannels())->firstWhere('name', $name);
    }
}
