<script>
    import { createEventDispatcher } from 'svelte';
    import { X, RefreshCw } from 'lucide-svelte';

    export let job;

    const dispatch = createEventDispatcher();

    const statusColors = {
        completed: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
        failed: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
        pending: 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
    };

    function close() {
        dispatch('close');
    }

    function retry() {
        // TODO: call retryJob(job.id) from api.js
        close();
    }
</script>

<div
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm"
    on:click={close}
    on:keydown={(e) => e.key === 'Escape' && close()}
    role="dialog"
    aria-modal="true"
    tabindex="-1"
>
    <div
        class="bg-white dark:bg-gray-900 rounded-xl shadow-2xl w-full max-w-2xl mx-4 overflow-hidden"
        on:click|stopPropagation
        role="document"
    >
        <!-- Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-800">
            <div class="flex items-center gap-3">
                <h3 class="font-semibold text-gray-900 dark:text-gray-100">{job.name}</h3>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {statusColors[job.status]}">
                    {job.status}
                </span>
            </div>
            <button
                on:click={close}
                class="p-1.5 rounded-md text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
            >
                <X class="w-4 h-4" />
            </button>
        </div>

        <!-- Body -->
        <div class="px-6 py-4 space-y-4">
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-0.5">ID</p>
                    <p class="font-mono font-medium text-gray-900 dark:text-gray-100">#{job.id}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-0.5">Queue</p>
                    <p class="font-mono font-medium text-gray-900 dark:text-gray-100">{job.queue}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-0.5">Created at</p>
                    <p class="text-gray-900 dark:text-gray-100">{job.created_at}</p>
                </div>
            </div>

            {#if job.exception}
                <div>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">Exception</p>
                    <pre class="p-4 rounded-lg bg-red-50 dark:bg-red-900/10 text-red-700 dark:text-red-400 text-xs overflow-x-auto whitespace-pre-wrap font-mono">{job.exception}</pre>
                </div>
            {/if}
        </div>

        <!-- Footer -->
        <div class="flex items-center justify-end gap-2 px-6 py-4 border-t border-gray-100 dark:border-gray-800">
            <button
                on:click={close}
                class="px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-400 rounded-md hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
            >
                Close
            </button>
            {#if job.status === 'failed'}
                <button
                    on:click={retry}
                    class="flex items-center gap-2 px-4 py-2 text-sm font-medium bg-gray-900 dark:bg-gray-100 text-white dark:text-gray-900 rounded-md hover:bg-gray-800 dark:hover:bg-gray-200 transition-colors"
                >
                    <RefreshCw class="w-3.5 h-3.5" />
                    Retry Job
                </button>
            {/if}
        </div>
    </div>
</div>
