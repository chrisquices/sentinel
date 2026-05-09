<?php

namespace Chrisquices\Sentinel\Helpers;

class SystemHelper
{
    public static function formatCores(int $cores): string
    {
        return strval($cores);
    }

    public static function formatBytes(int $bytes): string
    {
        if ($bytes >= 1024 ** 3) return round($bytes / 1024 ** 3, 2) . ' GB';
        if ($bytes >= 1024 ** 2) return round($bytes / 1024 ** 2, 2) . ' MB';
        if ($bytes >= 1024)      return round($bytes / 1024, 2)      . ' KB';
        return $bytes . ' B';
    }

    public static function formatPercentage(float $value): string
    {
        return round($value, 2) . '%';
    }

    public static function formatTime(int $timestamp): string
    {
        return date('h:i:s A', $timestamp);
    }
}