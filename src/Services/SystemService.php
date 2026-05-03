<?php

namespace Chrisquices\VulcanSentinel\Services;

use Chrisquices\VulcanSentinel\Helpers\SystemHelper;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class SystemService
{
    public static function get(): array
    {

        // CPU
        $cpuCores = self::getCpuCores();
        $cpuCoresFormatted = SystemHelper::formatCores($cpuCores);

        $cpuUsage = self::getCpuUsage();
        $cpuUsageFormatted = SystemHelper::formatPercentage($cpuUsage);

        $cpuHistory = self::getCpuHistory();

        // Memory
        $totalRam = self::getTotalRam();
        $totalRamFormatted = SystemHelper::formatBytes($totalRam['value']);

        $usedRam = self::getUsedRam();
        $usedRamFormatted = SystemHelper::formatBytes($usedRam['value']);

        $availableRam = self::getAvailableRam();
        $availableRamFormatted = SystemHelper::formatBytes($availableRam['value']);

        // Storage
        $totalStorage = self::getTotalStorage();
        $totalStorageFormatted = SystemHelper::formatBytes($totalStorage['value']);

        $usedStorage = self::getUsedStorage();
        $usedStorageFormatted = SystemHelper::formatBytes($usedStorage['value']);

        $availStorage = self::getAvailableStorage();
        $availStorageFormatted = SystemHelper::formatBytes($availStorage['value']);

        return [
            'cpu' => [
                'cores' => $cpuCores,
                'coresFormatted' => $cpuCoresFormatted,
                'usage' => $cpuUsage,
                'usageFormatted' => $cpuUsageFormatted,
                'history' => $cpuHistory,
            ],
            'memory' => [
                'total' => $totalRam['value'],
                'totalFormatted' => $totalRamFormatted,
                'used' => $usedRam['value'],
                'usedFormatted' => $usedRamFormatted,
                'available' => $availableRam['value'],
                'availableFormatted' => $availableRamFormatted,
            ],
            'storage' => [
                'total' => $totalStorage['value'],
                'totalFormatted' => $totalStorageFormatted,
                'used' => $usedStorage['value'],
                'usedFormatted' => $usedStorageFormatted,
                'available' => $availStorage['value'],
                'availableFormatted' => $availStorageFormatted,
            ],
        ];
    }

    // region --- CPU --------------------------------------------------------------------------------------------------
    public static function getCpuCores(): int
    {
        if (PHP_OS_FAMILY === 'Linux' && is_readable('/proc/cpuinfo')) {
            return max(substr_count(file_get_contents('/proc/cpuinfo'), 'processor'), 1);
        }

        if (PHP_OS_FAMILY === 'Darwin') {
            $cores = shell_exec('/usr/sbin/sysctl -n hw.logicalcpu 2>/dev/null');
            return max((int)trim($cores ?? '1'), 1);
        }

        return 1;
    }

    public static function getCpuUsage(): float
    {
        $usage = 0.0;

        if (PHP_OS_FAMILY === 'Linux' && is_readable('/proc/stat')) {
            $stat1 = file_get_contents('/proc/stat');
            usleep(100000);
            $stat2 = file_get_contents('/proc/stat');

            preg_match('/^cpu\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)/', $stat1, $m1);
            preg_match('/^cpu\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)/', $stat2, $m2);

            $idle1 = $m1[4];
            $total1 = array_sum(array_slice($m1, 1));
            $idle2 = $m2[4];
            $total2 = array_sum(array_slice($m2, 1));
            $totalDiff = $total2 - $total1;
            $idleDiff = $idle2 - $idle1;

            $usage = $totalDiff > 0 ? round((1 - $idleDiff / $totalDiff) * 100, 1) : 0.0;
        } elseif (PHP_OS_FAMILY === 'Darwin') {
            $output = shell_exec('top -l 2 -s 0 | grep "CPU usage" | tail -1 2>/dev/null');
            if ($output && preg_match('/([\d.]+)%\s+user.*?([\d.]+)%\s+sys/', $output, $m)) {
                $usage = round((float)$m[1] + (float)$m[2], 1);
            }
        }

        self::storeCpuSample($usage);

        return $usage;
    }

    public static function getCpuHistory(): array
    {
        return Cache::get('cpu_samples', []);
    }

    private static function storeCpuSample(float $usage): void
    {
        $samples = Cache::get('cpu_samples', []);

        $samples[] = [
            'time' => time(),
            'timeFormatted' => SystemHelper::formatTime(time()),
            'usage' => $usage,
            'usageFormatted' => SystemHelper::formatPercentage($usage),
        ];

        if (count($samples) > 3600) {
            $samples = array_slice($samples, -3600);
        }

        Cache::put('cpu_samples', $samples, now()->addHour());
    }

    public static function forgetCpuHistory(): void
    {
        Cache::forget('cpu_samples');
    }

    // endregion

    // region --- Memory ---------------------------------------------------------------------------------------------------
    public static function getTotalRam(): array
    {
        if (PHP_OS_FAMILY === 'Linux') {
            preg_match('/MemTotal:\s+(\d+)/', file_get_contents('/proc/meminfo'), $m);
            $bytes = isset($m[1]) ? (int)$m[1] * 1024 : 0;
        } elseif (PHP_OS_FAMILY === 'Darwin') {
            $bytes = (int)trim(shell_exec('/usr/sbin/sysctl -n hw.memsize') ?? '0');
        } else {
            $bytes = 0;
        }

        return ['value' => $bytes, 'formatted' => SystemHelper::formatBytes($bytes)];
    }

    public static function getUsedRam(): array
    {
        if (PHP_OS_FAMILY === 'Linux') {
            $meminfo = file_get_contents('/proc/meminfo');
            preg_match('/MemTotal:\s+(\d+)/m', $meminfo, $total);
            preg_match('/MemAvailable:\s+(\d+)/m', $meminfo, $available);
            $bytes = isset($total[1], $available[1]) ? ((int)$total[1] - (int)$available[1]) * 1024 : 0;
        } elseif (PHP_OS_FAMILY === 'Darwin') {
            $output = shell_exec('/usr/bin/vm_stat 2>/dev/null');
            preg_match('/Anonymous pages:\s+(\d+)/', $output, $anonymous);
            preg_match('/Pages wired down:\s+(\d+)/', $output, $wired);
            preg_match('/Pages occupied by compressor:\s+(\d+)/', $output, $compressed);
            $bytes = (
                    (int)($anonymous[1] ?? 0) +
                    (int)($wired[1] ?? 0) +
                    (int)($compressed[1] ?? 0)
                ) * 16384;
        } else {
            $bytes = 0;
        }

        return ['value' => $bytes, 'formatted' => SystemHelper::formatBytes($bytes)];
    }

    public static function getAvailableRam(): array
    {
        $bytes = max(self::getTotalRam()['value'] - self::getUsedRam()['value'], 0);

        return ['value' => $bytes, 'formatted' => SystemHelper::formatBytes($bytes)];
    }

    // endregion

    // region --- Storage ---------------------------------------------------------------------------------------------------
    public static function getTotalStorage(): array
    {
        $bytes = disk_total_space('/');
        return ['value' => $bytes, 'formatted' => SystemHelper::formatBytes($bytes)];
    }

    public static function getUsedStorage(): array
    {
        $bytes = disk_total_space('/') - disk_free_space('/');
        return ['value' => $bytes, 'formatted' => SystemHelper::formatBytes($bytes)];
    }

    public static function getAvailableStorage(): array
    {
        $bytes = disk_free_space('/');
        return ['value' => $bytes, 'formatted' => SystemHelper::formatBytes($bytes)];
    }

    // endregion
}
