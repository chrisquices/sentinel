<?php

namespace Chrisquices\VulcanSentinel\Helpers;

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
        if (!$datetime) return '—';
        return date('Y-m-d h:i:s A', strtotime($datetime));
    }

    public static function truncateException(string $exception, int $length = 120): string
    {
        $firstLine = strtok($exception, "\n");
        return strlen($firstLine) > $length
            ? substr($firstLine, 0, $length) . '…'
            : $firstLine;
    }
}