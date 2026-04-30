<script>
    import { createEventDispatcher } from 'svelte';

    const dispatch = createEventDispatcher();

    const mockTasks = [
        {
            id: 1,
            command: 'app:send-daily-reminders',
            expression: '0 9 * * *',
            last_ran: '2024-01-15 09:00:01',
            next_run: '2024-01-16 09:00:00',
            status: 'success',
            output: 'Sent 42 reminders successfully.',
            exception: null,
        },
        {
            id: 2,
            command: 'app:cleanup-temp-files',
            expression: '0 0 * * *',
            last_ran: '2024-01-15 00:00:02',
            next_run: '2024-01-16 00:00:00',
            status: 'failed',
            output: '',
            exception: "ErrorException: Permission denied on /tmp/app-cache\n#0 /app/Console/Commands/CleanupTempFiles.php(34): file_put_contents()\n#1 /vendor/laravel/framework/src/Illuminate/Console/Command.php(182): App\\Console\\Commands\\CleanupTempFiles->handle()",
        },
        {
            id: 3,
            command: 'app:sync-external-data',
            expression: '*/5 * * * *',
            last_ran: '2024-01-15 10:25:00',
            next_run: '2024-01-15 10:30:00',
            status: 'success',
            output: 'Synced 128 records from external API.',
            exception: null,
        },
        {
            id: 4,
            command: 'app:generate-sitemap',
            expression: '0 3 * * 0',
            last_ran: '2024-01-14 03:00:01',
            next_run: '2024-01-21 03:00:00',
            status: 'success',
            output: 'Generated sitemap with 1,204 URLs.',
            exception: null,
        },
        {
            id: 5,
            command: 'app:prune-audit-logs',
            expression: '0 1 1 * *',
            last_ran: '2024-01-01 01:00:02',
            next_run: '2024-02-01 01:00:00',
            status: 'failed',
            output: '',
            exception: 'PDOException: SQLSTATE[HY000]: General error: 2006 MySQL server has gone away',
        },
    ];
</script>

<div class="overflow-x-auto">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-gray-100 dark:border-gray-800">
                <th class="text-left py-3 px-4 text-xs font-medium text-gray-500 dark:text-gray-400">Command</th>
                <th class="text-left py-3 px-4 text-xs font-medium text-gray-500 dark:text-gray-400">Expression</th>
                <th class="text-left py-3 px-4 text-xs font-medium text-gray-500 dark:text-gray-400">Last Ran</th>
                <th class="text-left py-3 px-4 text-xs font-medium text-gray-500 dark:text-gray-400">Next Run</th>
                <th class="text-left py-3 px-4 text-xs font-medium text-gray-500 dark:text-gray-400">Status</th>
            </tr>
        </thead>
        <tbody>
            {#each mockTasks as task (task.id)}
                <tr
                    on:click={() => dispatch('selectTask', task)}
                    class="cursor-pointer transition-colors border-b border-gray-50 dark:border-gray-800/50 last:border-0
                        {task.status === 'failed'
                            ? 'bg-red-50/40 dark:bg-red-900/10 hover:bg-red-50 dark:hover:bg-red-900/20'
                            : 'hover:bg-gray-50 dark:hover:bg-gray-800/50'}"
                >
                    <td class="py-3 px-4 font-mono text-xs text-gray-900 dark:text-gray-100">{task.command}</td>
                    <td class="py-3 px-4 font-mono text-xs text-gray-500 dark:text-gray-400">{task.expression}</td>
                    <td class="py-3 px-4 text-xs text-gray-500 dark:text-gray-400">{task.last_ran}</td>
                    <td class="py-3 px-4 text-xs text-gray-500 dark:text-gray-400">{task.next_run}</td>
                    <td class="py-3 px-4">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                            {task.status === 'success'
                                ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'
                                : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'}">
                            {task.status}
                        </span>
                    </td>
                </tr>
            {/each}
        </tbody>
    </table>
</div>
