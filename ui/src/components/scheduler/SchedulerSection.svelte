<script>
    import SchedulerTable from './SchedulerTable.svelte';
    import SchedulerDialog from './SchedulerDialog.svelte';

    let selectedTask = null;
</script>

<section>
    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Scheduler</h2>
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800">
        <SchedulerTable on:selectTask={(e) => (selectedTask = e.detail)} />
    </div>
    {#if selectedTask}
        <SchedulerDialog task={selectedTask} on:close={() => (selectedTask = null)} />
    {/if}
</section>
