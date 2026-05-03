<?php

namespace Chrisquices\VulcanSentinel\Services;

use Chrisquices\VulcanSentinel\Helpers\RuntimeHelper;

class RuntimeService
{
    public static function getInfo(): array
    {
        $phpVersion        = PHP_VERSION;
        $sapi              = php_sapi_name() ?: 'unknown';
        $memoryLimit       = RuntimeHelper::formatIniBytes(ini_get('memory_limit'));
        $maxExecutionTime  = RuntimeHelper::formatSeconds((int) ini_get('max_execution_time'));
        $uploadMaxFilesize = RuntimeHelper::formatIniBytes(ini_get('upload_max_filesize'));
        $postMaxSize       = RuntimeHelper::formatIniBytes(ini_get('post_max_size'));
        $opcache           = self::getOpcache();
        $extensions        = get_loaded_extensions();
        sort($extensions);

        return [
            'phpVersion'        => $phpVersion,
            'sapi'              => $sapi,
            'memoryLimit'       => $memoryLimit,
            'maxExecutionTime'  => $maxExecutionTime,
            'uploadMaxFilesize' => $uploadMaxFilesize,
            'postMaxSize'       => $postMaxSize,
            'opcache'           => $opcache,
            'extensions'        => $extensions,
        ];
    }

    // region --- OPcache --------------------------------------------------------------------------------------------------
    private static function getOpcache(): array
    {
        $status  = function_exists('opcache_get_status') ? @opcache_get_status(false) : false;
        $enabled = is_array($status) && ($status['opcache_enabled'] ?? false);

        if (!$enabled) {
            return [
                'enabled'       => false,
                'hitRatio'      => null,
                'memoryUsed'    => null,
                'memoryFree'    => null,
                'cachedScripts' => null,
            ];
        }

        $stats  = $status['opcache_statistics'] ?? [];
        $memory = $status['memory_usage'] ?? [];

        $hits          = (int) ($stats['hits'] ?? 0);
        $misses        = (int) ($stats['misses'] ?? 0);
        $total         = $hits + $misses;
        $hitRatio      = $total > 0 ? round($hits / $total * 100, 1) : null;
        $memoryUsed    = RuntimeHelper::formatBytes((int) ($memory['used_memory'] ?? 0));
        $memoryFree    = RuntimeHelper::formatBytes((int) ($memory['free_memory'] ?? 0));
        $cachedScripts = (int) ($stats['num_cached_scripts'] ?? 0);

        return [
            'enabled'       => $enabled,
            'hitRatio'      => $hitRatio,
            'memoryUsed'    => $memoryUsed,
            'memoryFree'    => $memoryFree,
            'cachedScripts' => $cachedScripts,
        ];
    }

    // endregion
}
