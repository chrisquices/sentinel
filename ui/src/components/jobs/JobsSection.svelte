<script>
    import JobsToolbar from './JobsToolbar.svelte';
    import JobsTable from './JobsTable.svelte';
    import JobDialog from './JobDialog.svelte';

    let filter = 'all';
    let selectedJob = null;
</script>

<section>
    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Jobs</h2>
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800">
        <JobsToolbar {filter} on:filterChange={(e) => (filter = e.detail)} />
        <JobsTable {filter} on:selectJob={(e) => (selectedJob = e.detail)} />
    </div>
    {#if selectedJob}
        <JobDialog job={selectedJob} on:close={() => (selectedJob = null)} />
    {/if}
</section>
