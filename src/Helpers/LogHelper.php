<?php

namespace Chrisquices\Sentinel\Helpers;

use Carbon\Carbon;

class LogHelper
{
    public static function detectLogFormat(string $path): string
    {
        if (!file_exists($path)) return 'plain';

        $handle = fopen($path, 'rb');
        if (!$handle) return 'plain';

        $line = fgets($handle);
        fclose($handle);

        if ($line === false) return 'plain';

        return str_starts_with(ltrim($line), '{') ? 'json' : 'plain';
    }

    public static function parseChunk(string $chunk, string $driver): array
    {
        if ($driver !== 'json') {
            // Trim any leading partial line (cursor may have landed mid-line)
            $firstNewline = strpos($chunk, "\n");
            if ($firstNewline !== false) {
                $chunk = substr($chunk, $firstNewline + 1);
            }
        }

        return $driver === 'json'
            ? self::parseJsonChunk($chunk)
            : self::parsePlainChunk($chunk);
    }

    private static function parsePlainChunk(string $chunk): array
    {
        // Laravel default format: [YYYY-MM-DD HH:MM:SS] env.LEVEL: message\nstack trace...
        $pattern = '/^\[(\d{4}-\d{2}-\d{2}[T ]\d{2}:\d{2}:\d{2}(?:\.\d+)?(?:[+-]\d{2}:\d{2}|Z)?)\]\s+\S+\.(\w+):\s+(.*?)(?=^\[|\z)/ms';

        preg_match_all($pattern, $chunk, $matches, PREG_SET_ORDER);

        $entries = [];
        foreach ($matches as $match) {
            $message = trim($match[3]);
            $lines = explode("\n", $message);
            $first = trim(array_shift($lines));
            $extra = trim(implode("\n", $lines));

            $entries[] = [
                'timestamp' => $match[1],
                'timestampFormatted' => self::formatTimestamp($match[1]),
                'level' => strtolower($match[2]),
                'message' => $first,
                'extra' => $extra ?: null,
            ];
        }

        return $entries;
    }

    private static function parseJsonChunk(string $chunk): array
    {
        $entries = [];

        foreach (explode("\n", $chunk) as $line) {
            $line = trim($line);
            if ($line === '') continue;

            $decoded = json_decode($line, true);
            if (!is_array($decoded)) continue;

            $entries[] = [
                'timestamp' => $decoded['datetime'] ?? $decoded['timestamp'] ?? null,
                'timestampFormatted' => self::formatTimestamp($decoded['datetime'] ?? $decoded['timestamp'] ?? null),
                'level' => strtolower($decoded['level_name'] ?? $decoded['level'] ?? 'info'),
                'message' => $decoded['message'] ?? '',
                'extra' => isset($decoded['context']) && $decoded['context']
                    ? json_encode($decoded['context'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
                    : null,
            ];
        }

        return $entries;
    }

    private static function formatTimestamp(?string $timestamp): ?string
    {
        if (!$timestamp) return null;

        try {
            $dt = Carbon::parse($timestamp);
            $diff = $dt->diffForHumans();

            return $dt->format('M j, Y g:i:s A') . ' (' . $diff . ')';
        } catch (\Exception) {
            return $timestamp;
        }
    }
}
