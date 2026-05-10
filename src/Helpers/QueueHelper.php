<?php

namespace Chrisquices\Sentinel\Helpers;

use Carbon\Carbon;

class QueueHelper
{
    public static function decodePayload(string $rawPayload): array
    {
        $payload = json_decode($rawPayload, true) ?? [];

        return [
            'class' => $payload['data']['commandName'] ?? $payload['job'] ?? 'Unknown',
            'displayName' => $payload['displayName'] ?? 'Unknown',
            'attempts' => $payload['attempts'] ?? 0,
            'queue' => $payload['queue'] ?? 'default',
        ];
    }

    public static function formatDateTime(?string $datetime): string
    {
        if (!$datetime) return '-';

        $dt = Carbon::parse($datetime);
        $seconds = (int) abs($dt->diffInSeconds(now()));

        if ($seconds < 60)      $rel = $seconds . 's ago';
        elseif ($seconds < 3600)    $rel = floor($seconds / 60) . 'm ago';
        elseif ($seconds < 86400)   $rel = floor($seconds / 3600) . 'h ago';
        elseif ($seconds < 604800)  $rel = floor($seconds / 86400) . 'd ago';
        elseif ($seconds < 2592000) $rel = floor($seconds / 604800) . 'w ago';
        elseif ($seconds < 31536000) $rel = floor($seconds / 2592000) . 'mo ago';
        else                        $rel = floor($seconds / 31536000) . 'y ago';

        return $dt->format('M j, Y g:i A') . ' (' . $rel . ')';
    }

    public static function truncateException(string $exception, int $length = 120): string
    {
        $firstLine = strtok($exception, "\n");
        return strlen($firstLine) > $length
            ? substr($firstLine, 0, $length) . '…'
            : $firstLine;
    }
}