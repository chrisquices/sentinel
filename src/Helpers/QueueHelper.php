<?php

namespace Chrisquices\VulcanSentinel\Helpers;

class QueueHelper
{
    /**
     * Extract a short class name from a fully qualified job class.
     * e.g. App\Jobs\SendWelcomeEmail => SendWelcomeEmail
     */
    public static function formatJobName(string $class): string
    {
        return class_basename($class);
    }

    /**
     * Normalize a job status string for display.
     */
    public static function formatStatus(string $status): string
    {
        return ucfirst($status);
    }

    /**
     * Decode a raw Laravel queue job payload.
     * Returns the job class and display name.
     */
    public static function decodePayload(string $rawPayload): array
    {
        $payload = json_decode($rawPayload, true) ?? [];

        return [
            'class'       => $payload['data']['commandName'] ?? $payload['job'] ?? 'Unknown',
            'displayName' => $payload['displayName'] ?? 'Unknown',
            'attempts'    => $payload['attempts'] ?? 0,
            'queue'       => $payload['queue'] ?? 'default',
        ];
    }

    /**
     * Format a Unix timestamp or datetime string for display.
     */
    public static function formatDateTime(?string $datetime): string
    {
        if (!$datetime) return '—';
        return date('Y-m-d h:i:s A', strtotime($datetime));
    }

    /**
     * Truncate a long exception message for table display.
     */
    public static function truncateException(string $exception, int $length = 120): string
    {
        $firstLine = strtok($exception, "\n");
        return strlen($firstLine) > $length
            ? substr($firstLine, 0, $length) . '…'
            : $firstLine;
    }
}