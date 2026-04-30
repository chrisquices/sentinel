<script>
    import { createEventDispatcher } from 'svelte';
    import { X } from 'lucide-svelte';

    export let log;

    const dispatch = createEventDispatcher();

    const levelColors = {
        error: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
        warning: 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
        info: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
        debug: 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400',
    };

    function close() {
        dispatch('close');
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
                <h3 class="font-semibold text-gray-900 dark:text-gray-100">Log Entry</h3>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {levelColors[log.level] ?? levelColors.debug}">
                    {log.level}
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
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-0.5">Date</p>
                <p class="text-sm text-gray-900 dark:text-gray-100">{log.created_at}</p>
            </div>

            <div>
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">Message</p>
                <p class="text-sm text-gray-900 dark:text-gray-100 leading-relaxed">{log.message}</p>
            </div>

            {#if log.exception}
                <div>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">Exception</p>
                    <pre class="p-4 rounded-lg bg-red-50 dark:bg-red-900/10 text-red-700 dark:text-red-400 text-xs overflow-x-auto whitespace-pre-wrap font-mono">{log.exception}</pre>
                </div>
            {/if}

            {#if log.context && Object.keys(log.context).length > 0}
                <div>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">Context</p>
                    <pre class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800 text-gray-700 dark:text-gray-300 text-xs overflow-x-auto font-mono">{JSON.stringify(log.context, null, 2)}</pre>
                </div>
            {/if}
        </div>

        <!-- Footer -->
        <div class="flex justify-end px-6 py-4 border-t border-gray-100 dark:border-gray-800">
            <button
                on:click={close}
                class="px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-400 rounded-md hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
            >
                Close
            </button>
        </div>
    </div>
</div>
