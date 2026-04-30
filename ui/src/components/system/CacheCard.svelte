<script>
    import { Zap } from 'lucide-svelte';

    const mock = {
        hitRate: 94.2,
        hits: 18430,
        misses: 1072,
        memoryUsed: '142 MB',
        driver: 'redis',
    };
</script>

<div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6">
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-2">
            <Zap class="w-4 h-4 text-yellow-500" />
            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Cache</h3>
        </div>
        {#if mock.driver === 'redis'}
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400">
                Redis only
            </span>
        {:else}
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                {mock.driver}
            </span>
        {/if}
    </div>

    <div class="mb-5">
        <div class="flex items-baseline gap-1 mb-2">
            <span class="text-3xl font-bold text-gray-900 dark:text-gray-100">{mock.hitRate}%</span>
            <span class="text-xs text-gray-400 dark:text-gray-500">hit rate</span>
        </div>
        <div class="w-full bg-gray-100 dark:bg-gray-800 rounded-full h-2">
            <div class="bg-yellow-400 h-2 rounded-full" style="width: {mock.hitRate}%"></div>
        </div>
    </div>

    <div class="space-y-2">
        <div class="flex justify-between text-xs">
            <span class="text-gray-500 dark:text-gray-400">Hits</span>
            <span class="font-semibold text-green-600 dark:text-green-400">{mock.hits.toLocaleString()}</span>
        </div>
        <div class="flex justify-between text-xs">
            <span class="text-gray-500 dark:text-gray-400">Misses</span>
            <span class="font-semibold text-red-500">{mock.misses.toLocaleString()}</span>
        </div>
        <div class="flex justify-between text-xs">
            <span class="text-gray-500 dark:text-gray-400">Memory used</span>
            <span class="font-semibold text-gray-900 dark:text-gray-100">{mock.memoryUsed}</span>
        </div>
    </div>
</div>
