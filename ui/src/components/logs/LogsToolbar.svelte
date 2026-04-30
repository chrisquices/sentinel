<script>
    import { createEventDispatcher } from 'svelte';
    import { Trash2 } from 'lucide-svelte';

    export let filter = 'all';

    const dispatch = createEventDispatcher();
    const filters = ['all', 'info', 'error', 'debug', 'warning'];

    function setFilter(f) {
        filter = f;
        dispatch('filterChange', f);
    }

    function deleteLogs() {
        if (confirm('Delete all logs? This cannot be undone.')) {
            // TODO: call deleteLogs() from api.js
        }
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
        on:click={deleteLogs}
        class="flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs font-medium text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
    >
        <Trash2 class="w-3 h-3" />
        Delete
    </button>
</div>
