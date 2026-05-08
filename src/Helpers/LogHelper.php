<?php

namespace Chrisquices\VulcanSentinel\Helpers;

use Carbon\Carbon;

class LogHelper
{
    public static function detectFormat(string $line): string
    {
        $trimmed = ltrim($line);
        if ($trimmed === '' || $trimmed[0] !== '{') {
            return 'plaintext';
        }
        return json_decode($line, true) !== null ? 'json' : 'plaintext';
    }

    public static function parsePlaintextEntry(array $lines): array
    {
        $first = $lines[0] ?? '';

        if (preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] (\w+)\.(\w+): (.*)$/', $first, $m)) {
            return [
                'timestamp'   => $m[1],
                'environment' => $m[2],
                'level'       => strtolower($m[3]),
                'message'     => $m[4],
                'extra'       => count($lines) > 1 ? implode("\n", array_slice($lines, 1)) : null,
            ];
        }

        return [
            'timestamp'   => null,
            'environment' => null,
            'level'       => 'debug',
            'message'     => $first,
            'extra'       => count($lines) > 1 ? implode("\n", array_slice($lines, 1)) : null,
        ];
    }

    public static function parseJsonEntry(string $line): array
    {
        $data = json_decode($line, true) ?? [];

        return [
            'timestamp' => isset($data['datetime'])
                ? Carbon::parse($data['datetime'])->format('M j, Y H:i:s')
                : null,
            'environment' => $data['channel'] ?? null,
            'level'       => strtolower($data['level_name'] ?? 'debug'),
            'message'     => $data['message'] ?? '',
            'extra'       => !empty($data['context']) ? json_encode($data['context'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : null,
        ];
    }

    public static function levelColor(string $level): string
    {
        return match (strtolower($level)) {
            'emergency', 'alert', 'critical', 'error' => 'border-red-500/30 bg-red-500/10 text-red-500 dark:text-red-400',
            'warning'                                  => 'border-amber-500/30 bg-amber-500/10 text-amber-600 dark:text-amber-400',
            'notice', 'info'                           => 'border-blue-500/30 bg-blue-500/10 text-blue-600 dark:text-blue-400',
            default                                    => 'border-zinc-500/30 bg-zinc-500/10 text-zinc-500 dark:text-zinc-400',
        };
    }
}
