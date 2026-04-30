<script>
    import { ListChecks } from 'lucide-svelte';

    const mock = {
        sizes: { default: 14, emails: 3, payments: 0, notifications: 8 },
        failed: 7,
        throughput: 142,
    };
</script>

<div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6">
    <div class="flex items-center gap-2 mb-4">
        <ListChecks class="w-4 h-4 text-blue-500" />
        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Queues</h3>
    </div>

    <div class="space-y-2.5 mb-4">
        {#each Object.entries(mock.sizes) as [queue, count]}
            <div class="flex items-center justify-between">
                <span class="text-xs font-mono text-gray-500 dark:text-gray-400">{queue}</span>
                <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">{count}</span>
            </div>
        {/each}
    </div>

    <div class="pt-3 border-t border-gray-100 dark:border-gray-800 space-y-2">
        <div class="flex justify-between text-xs">
            <span class="text-gray-500 dark:text-gray-400">Failed jobs</span>
            <span class="font-semibold text-red-500">{mock.failed}</span>
        </div>
        <div class="flex justify-between text-xs">
            <span class="text-gray-500 dark:text-gray-400">Throughput (1h)</span>
            <span class="font-semibold text-gray-900 dark:text-gray-100">{mock.throughput} jobs</span>
        </div>
    </div>
</div>
