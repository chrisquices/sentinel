<script>
    import { createEventDispatcher } from 'svelte';

    export let filter = 'all';

    const dispatch = createEventDispatcher();

    const mockLogs = [
        { id: 1, level: 'error', message: 'Connection refused to database server at 127.0.0.1:3306', exception: "PDOException: SQLSTATE[HY000] [2002] Connection refused\n#0 /vendor/laravel/framework/src/Illuminate/Database/Connectors/Connector.php(70): PDO->__construct()\n#1 /app/Http/Controllers/DashboardController.php(28): Illuminate\\Database\\Connectors\\Connector->createConnection()", created_at: '2024-01-15 10:30:12', context: {} },
        { id: 2, level: 'info', message: 'User #4821 authenticated successfully via OAuth', exception: null, created_at: '2024-01-15 10:29:44', context: { user_id: 4821, provider: 'google' } },
        { id: 3, level: 'warning', message: 'Rate limit approaching threshold for IP 192.168.1.42 (85/100 requests)', exception: null, created_at: '2024-01-15 10:28:30', context: { ip: '192.168.1.42', count: 85, limit: 100 } },
        { id: 4, level: 'debug', message: 'Cache miss for key: user_profile_4821', exception: null, created_at: '2024-01-15 10:27:15', context: { key: 'user_profile_4821', driver: 'redis' } },
        { id: 5, level: 'error', message: 'Failed to send email to user@example.com: Mailgun API returned 400', exception: 'RuntimeException: Mailgun API error: The from address does not comply with RFC 2822', created_at: '2024-01-15 10:26:00', context: {} },
        { id: 6, level: 'info', message: 'Scheduled task app:sync-data completed in 2.3s', exception: null, created_at: '2024-01-15 10:25:05', context: { duration: 2.3 } },
        { id: 7, level: 'warning', message: 'Slow query detected (4.2s): SELECT * FROM orders WHERE user_id = ?', exception: null, created_at: '2024-01-15 10:24:10', context: { duration: 4.2, threshold: 2 } },
        { id: 8, level: 'debug', message: 'Queue job dispatched: ProcessPaymentJob to payments queue', exception: null, created_at: '2024-01-15 10:23:00', context: { job: 'ProcessPaymentJob', queue: 'payments' } },
    ];

    const levelColors = {
        error: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
        warning: 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
        info: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
        debug: 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400',
    };

    $: rows = filter === 'all' ? mockLogs : mockLogs.filter((l) => l.level === filter);
</script>

<div class="overflow-x-auto">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-gray-100 dark:border-gray-800">
                <th class="text-left py-3 px-4 text-xs font-medium text-gray-500 dark:text-gray-400 w-24">Level</th>
                <th class="text-left py-3 px-4 text-xs font-medium text-gray-500 dark:text-gray-400">Message</th>
                <th class="text-left py-3 px-4 text-xs font-medium text-gray-500 dark:text-gray-400 whitespace-nowrap">Date</th>
            </tr>
        </thead>
        <tbody>
            {#each rows as log (log.id)}
                <tr
                    on:click={() => dispatch('selectLog', log)}
                    class="cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors border-b border-gray-50 dark:border-gray-800/50 last:border-0"
                >
                    <td class="py-3 px-4">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {levelColors[log.level] ?? levelColors.debug}">
                            {log.level}
                        </span>
                    </td>
                    <td class="py-3 px-4 text-gray-700 dark:text-gray-300 max-w-xl truncate">{log.message}</td>
                    <td class="py-3 px-4 text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap">{log.created_at}</td>
                </tr>
            {/each}
        </tbody>
    </table>
    {#if rows.length === 0}
        <div class="py-12 text-center text-sm text-gray-400 dark:text-gray-500">No logs found.</div>
    {/if}
</div>
