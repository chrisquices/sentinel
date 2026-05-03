<?php

namespace Chrisquices\VulcanSentinel\Helpers;

use Carbon\Carbon;

class SchedulerHelper
{
    public static function humanCron(string $expression): string
    {
        $parts = explode(' ', trim($expression));
        if (count($parts) !== 5) return $expression;

        [$minute, $hour, $day, $month, $weekday] = $parts;

        if ($expression === '* * * * *') return 'Every minute';

        if (preg_match('/^\*\/(\d+)$/', $minute, $m) && $hour === '*' && $day === '*' && $month === '*' && $weekday === '*') {
            return "Every {$m[1]} minutes";
        }

        if ($minute === '0' && $hour === '*' && $day === '*' && $month === '*' && $weekday === '*') {
            return 'Every hour';
        }

        if (preg_match('/^\d+$/', $minute) && $hour === '*' && $day === '*' && $month === '*' && $weekday === '*') {
            return "Every hour at :{$minute}";
        }

        if (preg_match('/^\d+$/', $minute) && preg_match('/^\d+$/', $hour) && $day === '*' && $month === '*' && $weekday === '*') {
            return 'Daily at ' . self::formatTime((int) $hour, (int) $minute);
        }

        if (preg_match('/^\d+$/', $minute) && preg_match('/^\d+$/', $hour) && $day === '*' && $month === '*' && preg_match('/^\d+$/', $weekday)) {
            $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
            $dayName = $days[(int) $weekday] ?? $weekday;
            return "Every {$dayName} at " . self::formatTime((int) $hour, (int) $minute);
        }

        if (preg_match('/^\d+$/', $minute) && preg_match('/^\d+$/', $hour) && preg_match('/^\d+$/', $day) && $month === '*' && $weekday === '*') {
            return "Monthly on day {$day} at " . self::formatTime((int) $hour, (int) $minute);
        }

        return $expression;
    }

    private static function formatTime(int $hour, int $minute): string
    {
        $ampm = $hour >= 12 ? 'PM' : 'AM';
        $h = $hour % 12 ?: 12;
        return sprintf('%d:%02d %s', $h, $minute, $ampm);
    }

    public static function relativeTime(Carbon|string|null $datetime): string
    {
        if (!$datetime) return '—';

        $carbon = $datetime instanceof Carbon ? $datetime : Carbon::parse($datetime);
        $diff = (int) now()->diffInSeconds($carbon, false);
        $abs = abs($diff);

        if ($abs < 60) return $diff >= 0 ? 'in a moment' : 'just now';

        if ($abs < 3600) {
            $m = (int) ($abs / 60);
            return $diff >= 0 ? "in {$m}m" : "{$m}m ago";
        }

        if ($abs < 86400) {
            $h = (int) ($abs / 3600);
            return $diff >= 0 ? "in {$h}h" : "{$h}h ago";
        }

        $d = (int) ($abs / 86400);
        return $diff >= 0 ? "in {$d}d" : "{$d}d ago";
    }
}
