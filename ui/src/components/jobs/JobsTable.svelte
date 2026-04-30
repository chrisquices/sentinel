<script>
    import { createEventDispatcher } from 'svelte';
    import { RefreshCw } from 'lucide-svelte';

    export let filter = 'all';

    const dispatch = createEventDispatcher();

    const mockJobs = [
        { id: 1, name: 'SendWelcomeEmailJob', queue: 'emails', status: 'completed', created_at: '2024-01-15 10:30:00', exception: null },
        { id: 2, name: 'ProcessPaymentJob', queue: 'payments', status: 'failed', created_at: '2024-01-15 10:28:00', exception: "Stripe\\Exception\\CardException: Your card was declined.\n#0 /app/Jobs/ProcessPaymentJob.php(52): Stripe\\Charge::create()\n#1 /vendor/laravel/framework/src/Illuminate/Queue/Jobs/Job.php(98): App\\Jobs\\ProcessPaymentJob->handle()" },
        { id: 3, name: 'GenerateMonthlyReportJob', queue: 'default', status: 'pending', created_at: '2024-01-15 10:25:00', exception: null },
        { id: 4, name: 'SendNotificationJob', queue: 'notifications', status: 'completed', created_at: '2024-01-15 10:20:00', exception: null },
        { id: 5, name: 'SyncInventoryJob', queue: 'default', status: 'failed', created_at: '2024-01-15 10:15:00', exception: 'RuntimeException: External API timeout after 30s.' },
        { id: 6, name: 'ResizeImageJob', queue: 'media', status: 'completed', created_at: '2024-01-15 10:10:00', exception: null },
        { id: 7, name: 'ImportCsvJob', queue: 'default', status: 'pending', created_at: '2024-01-15 10:05:00', exception: null },
    ];

    const statusColors = {
        completed: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
        failed: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
        pending: 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
    };

    $: rows = filter === 'all' ? mockJobs : mockJobs.filter((j) => j.status === filter);

    function retryJob(e, job) {
        e.stopPropagation();
        // TODO: call retryJob(job.id) from api.js
    }
</script>

<div class="overflow-x-auto">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-gray-100 dark:border-gray-800">
                <th class="text-left py-3 px-4 text-xs font-medium text-gray-500 dark:text-gray-400">ID</th>
                <th class="text-left py-3 px-4 text-xs font-medium text-gray-500 dark:text-gray-400">Name</th>
                <th class="text-left py-3 px-4 text-xs font-medium text-gray-500 dark:text-gray-400">Queue</th>
                <th class="text-left py-3 px-4 text-xs font-medium text-gray-500 dark:text-gray-400">Status</th>
                <th class="text-left py-3 px-4 text-xs font-medium text-gray-500 dark:text-gray-400">Created</th>
                <th class="text-left py-3 px-4 text-xs font-medium text-gray-500 dark:text-gray-400">Actions</th>
            </tr>
        </thead>
        <tbody>
            {#each rows as job (job.id)}
                <tr
                    on:click={() => dispatch('selectJob', job)}
                    class="cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors border-b border-gray-50 dark:border-gray-800/50 last:border-0"
                >
                    <td class="py-3 px-4 font-mono text-xs text-gray-400 dark:text-gray-500">#{job.id}</td>
                    <td class="py-3 px-4 font-medium text-gray-900 dark:text-gray-100">{job.name}</td>
                    <td class="py-3 px-4 font-mono text-xs text-gray-500 dark:text-gray-400">{job.queue}</td>
                    <td class="py-3 px-4">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {statusColors[job.status]}">
                            {job.status}
                        </span>
                    </td>
                    <td class="py-3 px-4 text-xs text-gray-500 dark:text-gray-400">{job.created_at}</td>
                    <td class="py-3 px-4">
                        {#if job.status === 'failed'}
                            <button
                                on:click={(e) => retryJob(e, job)}
                                class="flex items-center gap-1 px-2 py-1 text-xs rounded border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                            >
                                <RefreshCw class="w-3 h-3" />
                                Retry
                            </button>
                        {/if}
                    </td>
                </tr>
            {/each}
        </tbody>
    </table>
    {#if rows.length === 0}
        <div class="py-12 text-center text-sm text-gray-400 dark:text-gray-500">No jobs found.</div>
    {/if}
</div>
