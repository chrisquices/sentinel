<?php

namespace Chrisquices\VulcanSentinel\Services;

use Chrisquices\VulcanSentinel\Helpers\LogHelper;

class LogService
{
    private const CHUNK_SIZE = 524288; // 512 KB

    // region --- Channels -------------------------------------------------------------------------------------------------

    public static function getChannels(): array
    {
        $channels = config('logging.channels', []);
        $result   = [];

        foreach ($channels as $name => $config) {
            $driver = $config['driver'] ?? null;

            if ($driver === 'stack') {
                foreach ($config['channels'] ?? [] as $child) {
                    $childConfig = $channels[$child] ?? null;
                    if ($childConfig && in_array($childConfig['driver'] ?? null, ['single', 'daily'])) {
                        $result[] = self::resolveChannel($name, $childConfig);
                        break;
                    }
                }
            } elseif (in_array($driver, ['single', 'daily'])) {
                $result[] = self::resolveChannel($name, $config);
            }
        }

        return $result;
    }

    private static function resolveChannel(string $name, array $config): array
    {
        $driver = $config['driver'];
        $path   = $config['path'] ?? storage_path("logs/{$name}.log");

        if ($driver === 'daily' && !file_exists($path)) {
            $info     = pathinfo($path);
            $resolved = ($info['dirname'] ?? '') . DIRECTORY_SEPARATOR
                . ($info['filename'] ?? 'laravel') . '-' . date('Y-m-d') . '.' . ($info['extension'] ?? 'log');
            if (file_exists($resolved)) {
                $path = $resolved;
            }
        }

        return [
            'name'   => $name,
            'driver' => $driver,
            'path'   => $path,
        ];
    }

    // endregion

    // region --- Entries --------------------------------------------------------------------------------------------------

    public static function getEntries(string $path, ?int $cursor = null, int $limit = 20, string $level = ''): array
    {
        if (!file_exists($path) || filesize($path) === 0) {
            return ['entries' => [], 'cursor' => null, 'hasMore' => false, 'tailCursor' => 0];
        }

        $fileSize = filesize($path);
        $readEnd  = min($cursor ?? $fileSize, $fileSize); // rotation guard

        if ($readEnd === 0) {
            return ['entries' => [], 'cursor' => null, 'hasMore' => false, 'tailCursor' => $fileSize];
        }

        $chunkStart = max(0, $readEnd - self::CHUNK_SIZE);

        $fh    = fopen($path, 'rb');
        fseek($fh, $chunkStart);
        $chunk = fread($fh, $readEnd - $chunkStart);
        fclose($fh);

        $rawEntries = self::splitEntries($chunk);

        // First entry may be incomplete when not reading from file start
        if ($chunkStart > 0) {
            array_shift($rawEntries);
        }

        $allEntries = [];
        foreach ($rawEntries as $raw) {
            $entry = self::parseRaw($raw);
            if ($level === '' || $entry['level'] === strtolower($level)) {
                $allEntries[] = $entry;
            }
        }

        $entries = array_slice(array_reverse($allEntries), 0, $limit);
        $hasMore = $chunkStart > 0;

        return [
            'entries'    => $entries,
            'cursor'     => $hasMore ? $chunkStart : null,
            'hasMore'    => $hasMore,
            'tailCursor' => $fileSize,
        ];
    }

    public static function getTailEntries(string $path, int $tailCursor, string $level = ''): array
    {
        if (!file_exists($path)) {
            return ['entries' => [], 'tailCursor' => 0];
        }

        $fileSize = filesize($path);

        if ($tailCursor >= $fileSize) {
            return ['entries' => [], 'tailCursor' => $tailCursor];
        }

        $fh    = fopen($path, 'rb');
        fseek($fh, $tailCursor);
        $chunk = fread($fh, $fileSize - $tailCursor);
        fclose($fh);

        $rawEntries = self::splitEntries($chunk);
        $entries    = [];

        foreach ($rawEntries as $raw) {
            $entry = self::parseRaw($raw);
            if ($level === '' || $entry['level'] === strtolower($level)) {
                $entries[] = $entry;
            }
        }

        return ['entries' => $entries, 'tailCursor' => $fileSize];
    }

    public static function clearLog(string $path): bool
    {
        if (!file_exists($path)) {
            return false;
        }
        file_put_contents($path, '');
        return true;
    }

    // endregion

    // region --- Parsing --------------------------------------------------------------------------------------------------

    private static function splitEntries(string $buffer): array
    {
        $lines   = explode("\n", $buffer);
        $entries = [];
        $current = [];

        foreach ($lines as $line) {
            $trimmed = rtrim($line);
            if ($trimmed === '') continue;

            if (self::isEntryStart($trimmed) && $current) {
                $entries[] = implode("\n", $current);
                $current   = [$trimmed];
            } else {
                $current[] = $trimmed;
            }
        }

        if ($current) {
            $entries[] = implode("\n", $current);
        }

        return $entries;
    }

    private static function isEntryStart(string $line): bool
    {
        if (preg_match('/^\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\]/', $line)) {
            return true;
        }
        $trimmed = ltrim($line);
        return $trimmed !== '' && $trimmed[0] === '{';
    }

    private static function parseRaw(string $raw): array
    {
        $lines  = explode("\n", $raw);
        $first  = $lines[0] ?? '';
        $format = LogHelper::detectFormat($first);

        return $format === 'json'
            ? LogHelper::parseJsonEntry($first)
            : LogHelper::parsePlaintextEntry($lines);
    }

    // endregion
}
