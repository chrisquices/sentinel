<?php

namespace Chrisquices\Sentinel\Services;

use Chrisquices\Sentinel\Helpers\LogHelper;

class LogService
{
    private const CHUNK_SIZE = 524288; // 512 KB

    public static function get(): array
    {
        $channels = self::getChannels();
        $first = $channels[0]['name'] ?? null;

        $pollInterval = (int) config('sentinel.poll_interval', 3);

        if (!$first) {
            return ['channels' => [], 'entries' => [], 'total' => 0, 'tailCursor' => 0, 'pollInterval' => $pollInterval];
        }

        $logs = self::getLogs($first, 1, null);

        return [
            'channels'     => $channels,
            'entries'      => $logs['entries'],
            'total'        => $logs['total'],
            'tailCursor'   => $logs['tailCursor'],
            'perPage'      => $logs['perPage'],
            'pollInterval' => $pollInterval,
        ];
    }

    // region --- Channels -------------------------------------------------------------------------------------------------

    public static function getChannels(): array
    {
        $channels = config('logging.channels', []);
        $result = [];

        foreach ($channels as $name => $config) {
            $driver = $config['driver'] ?? null;

            if ($driver === 'stack') {
                foreach ($config['channels'] ?? [] as $child) {
                    $childConfig = $channels[$child] ?? null;
                    if ($childConfig && in_array($childConfig['driver'] ?? null, ['single', 'daily'])) {
                        $channel = self::resolveChannel($name, $childConfig);
                        if ($channel) $result[] = $channel;
                        break;
                    }
                }
            } elseif (in_array($driver, ['single', 'daily'])) {
                $channel = self::resolveChannel($name, $config);
                if ($channel) $result[] = $channel;
            }
        }

        return $result;
    }

    private static function getChannelByName(string $name): ?array
    {
        foreach (self::getChannels() as $channel) {
            if ($channel['name'] === $name) {
                return $channel;
            }
        }

        return null;
    }

    private static function resolveChannel(string $name, array $config): ?array
    {
        $driver = $config['driver'];
        $path = $config['path'] ?? storage_path("logs/{$name}.log");

        if ($driver === 'daily' && !file_exists($path)) {
            $info = pathinfo($path);
            $resolved = ($info['dirname'] ?? '') . DIRECTORY_SEPARATOR
                . ($info['filename'] ?? 'laravel') . '-' . date('Y-m-d') . '.' . ($info['extension'] ?? 'log');
            $path = file_exists($resolved) ? $resolved : null;
        }

        if (!$path || !file_exists($path)) {
            return null;
        }

        return [
            'name' => $name,
            'driver' => LogHelper::detectLogFormat($path),
            'path' => $path,
        ];
    }

    // endregion

    // region --- Logs -----------------------------------------------------------------------------------------------------
    private static function buildLogIndex(string $path, string $driver): array
    {
        $handle = fopen($path, 'rb');
        if (!$handle) return [];

        $offsets = [];
        $offset  = 0;

        while (!feof($handle)) {
            $line = fgets($handle);
            if ($line === false) break;

            if ($driver === 'json') {
                if (str_starts_with(ltrim($line), '{')) {
                    $decoded = json_decode($line, true);
                    $level   = strtolower($decoded['level_name'] ?? $decoded['level'] ?? 'info');
                    $offsets[] = ['offset' => $offset, 'level' => $level];
                }
            } else {
                if (preg_match('/^\[(\d{4}-\d{2}-\d{2}).*\]\s+\S+\.(\w+):/i', $line, $m)) {
                    $offsets[] = ['offset' => $offset, 'level' => strtolower($m[2])];
                }
            }

            $offset = ftell($handle);
        }

        fclose($handle);

        return $offsets;
    }

    private static function getLogIndex(string $channel, string $path, string $driver): array
    {
        $fileSize = filesize($path);
        $cacheKey = self::logCacheKey($channel);

        $cached = cache()->get($cacheKey);

        if ($cached && $cached['fileSize'] === $fileSize) {
            return $cached['offsets'];
        }

        $offsets = self::buildLogIndex($path, $driver);

        cache()->put($cacheKey, ['fileSize' => $fileSize, 'offsets' => $offsets], now()->addHour());

        return $offsets;
    }

    public static function getLogs(string $channel, int $page, ?string $level, ?string $search = null): array
    {
        $channelConfig = self::getChannelByName($channel);

        $perPage = config('sentinel.pagination', 20);

        if (!$channelConfig || !file_exists($channelConfig['path'])) {
            return ['entries' => [], 'total' => 0, 'tailCursor' => 0, 'perPage' => $perPage];
        }

        $path     = $channelConfig['path'];
        $fileSize = filesize($path);

        if ($fileSize === 0) {
            return ['entries' => [], 'total' => 0, 'tailCursor' => 0, 'perPage' => $perPage];
        }

        $allOffsets = self::getLogIndex($channel, $path, $channelConfig['driver']);

        // Filter by level at index level
        $filtered = $level
            ? array_values(array_filter($allOffsets, fn($o) => $o['level'] === strtolower($level)))
            : $allOffsets;

        $reversed       = array_reverse($filtered);
        $allOffsetsFlat = array_column($allOffsets, 'offset');

        $handle = fopen($path, 'rb');
        if (!$handle) {
            return ['entries' => [], 'total' => count($filtered), 'tailCursor' => $fileSize, 'perPage' => $perPage];
        }

        // When searching, parse all candidates to filter on message content
        if ($search !== null && $search !== '') {
            $matched = [];

            foreach ($reversed as $item) {
                $offset     = $item['offset'];
                $position   = array_search($offset, $allOffsetsFlat);
                $nextOffset = $allOffsets[$position + 1]['offset'] ?? $fileSize;

                fseek($handle, $offset);
                $parsed = LogHelper::parseChunk(fread($handle, $nextOffset - $offset), $channelConfig['driver']);

                if (!empty($parsed) && stripos($parsed[0]['message'] ?? '', $search) !== false) {
                    $matched[] = $parsed[0];
                }
            }

            fclose($handle);

            return [
                'entries'    => array_slice($matched, ($page - 1) * $perPage, $perPage),
                'total'      => count($matched),
                'tailCursor' => $fileSize,
                'perPage'    => $perPage,
            ];
        }

        // Standard path: paginate at offset level, then parse only the page
        $pageOffsets = array_slice($reversed, ($page - 1) * $perPage, $perPage);

        if (empty($pageOffsets)) {
            fclose($handle);
            return ['entries' => [], 'total' => count($filtered), 'tailCursor' => $fileSize, 'perPage' => $perPage];
        }

        $entries = [];

        foreach ($pageOffsets as $item) {
            $offset     = $item['offset'];
            $position   = array_search($offset, $allOffsetsFlat);
            $nextOffset = $allOffsets[$position + 1]['offset'] ?? $fileSize;

            fseek($handle, $offset);
            $parsed = LogHelper::parseChunk(fread($handle, $nextOffset - $offset), $channelConfig['driver']);

            if (!empty($parsed)) {
                $entries[] = $parsed[0];
            }
        }

        fclose($handle);

        return [
            'entries'    => $entries,
            'total'      => count($filtered),
            'tailCursor' => $fileSize,
            'perPage'    => $perPage,
        ];
    }

    public static function getLogTail(string $channel, int $tailCursor, ?string $level): array
    {
        $channelConfig = self::getChannelByName($channel);

        if (!$channelConfig || !file_exists($channelConfig['path'])) {
            return ['entries' => [], 'tailCursor' => $tailCursor];
        }

        $path = $channelConfig['path'];
        $fileSize = filesize($path);

        if ($fileSize <= $tailCursor) {
            return ['entries' => [], 'tailCursor' => $tailCursor];
        }

        $handle = fopen($path, 'rb');
        if (!$handle) {
            return ['entries' => [], 'tailCursor' => $tailCursor];
        }

        fseek($handle, $tailCursor);
        $chunk = fread($handle, self::CHUNK_SIZE);
        fclose($handle);

        self::invalidateLogIndex($channel);

        $entries = LogHelper::parseChunk($chunk, $channelConfig['driver']);

        if ($level) {
            $entries = array_values(array_filter($entries, fn($e) => strcasecmp($e['level'], $level) === 0));
        }

        return [
            'entries' => $entries,
            'tailCursor' => $fileSize,
        ];
    }

    public static function clearLog(string $channel): void
    {
        $channelConfig = self::getChannelByName($channel);

        if (!$channelConfig || !file_exists($channelConfig['path'])) return;

        file_put_contents($channelConfig['path'], '');
        self::invalidateLogIndex($channel);
    }

    private static function logCacheKey(string $channel): string
    {
        return "sentinel:log_index:{$channel}";
    }

    private static function invalidateLogIndex(string $channel): void
    {
        cache()->forget(self::logCacheKey($channel));
    }

    // endregion
}