<script>
    import { createEventDispatcher } from 'svelte';
    import { RotateCcw } from 'lucide-svelte';

    export let filter = 'all';

    const dispatch = createEventDispatcher();
    const filters = ['all', 'pending', 'completed', 'failed'];

    function setFilter(f) {
        filter = f;
        dispatch('filterChange', f);
    }
</script>

<div class="flex items-center justify-between px-4 py-3 border-b border-gray-100 dark:border-gray-800">
    <div class="flex items-center gap-1">
        {#each filters as f}
            <button
                on:click={() => setFilter(f)}
                class="px-3 py-1.5 rounded-md text-xs font-medium capitalize transition-colors
                    {filter === f
                        ? 'bg-gray-900 dark:bg-gray-100 text-white dark:text-gray-900'
                        : 'text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800'}"
            >
                {f}
            </button>
        {/each}
    </div>
    <button
        on:click={() => setFilter('all')}
        class="flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs font-medium text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
    >
        <RotateCcw class="w-3 h-3" />
        Reset
    </button>
</div>
