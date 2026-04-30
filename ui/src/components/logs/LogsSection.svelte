<script>
    import LogsToolbar from './LogsToolbar.svelte';
    import LogsTable from './LogsTable.svelte';
    import LogDialog from './LogDialog.svelte';

    let filter = 'all';
    let selectedLog = null;
</script>

<section class="pb-10">
    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Logs</h2>
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800">
        <LogsToolbar {filter} on:filterChange={(e) => (filter = e.detail)} />
        <LogsTable {filter} on:selectLog={(e) => (selectedLog = e.detail)} />
    </div>
    {#if selectedLog}
        <LogDialog log={selectedLog} on:close={() => (selectedLog = null)} />
    {/if}
</section>
