<?php

namespace Chrisquices\VulcanSentinel\Helpers;

class RuntimeHelper
{
    public static function parseIniBytes(string $value): int
    {
        $value = trim($value);
        if ($value === '-1') return -1;

        $last = strtolower(substr($value, -1));
        $num  = (int) $value;

        return match ($last) {
            'g'     => $num * 1024 ** 3,
            'm'     => $num * 1024 ** 2,
            'k'     => $num * 1024,
            default => $num,
        };
    }

    public static function formatBytes(int $bytes): string
    {
        if ($bytes < 0)           return 'Unlimited';
        if ($bytes >= 1024 ** 3)  return round($bytes / 1024 ** 3, 2) . ' GB';
        if ($bytes >= 1024 ** 2)  return round($bytes / 1024 ** 2, 2) . ' MB';
        if ($bytes >= 1024)       return round($bytes / 1024, 2)      . ' KB';
        return $bytes . ' B';
    }

    public static function formatIniBytes(string $value): string
    {
        return self::formatBytes(self::parseIniBytes($value));
    }

    public static function formatSeconds(int $seconds): string
    {
        return $seconds === 0 ? 'Unlimited' : $seconds . 's';
    }
}
