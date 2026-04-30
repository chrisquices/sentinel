<script>
    import CpuCard from './CpuCard.svelte';
    import QueueCard from './QueueCard.svelte';
    import CacheCard from './CacheCard.svelte';
</script>

<section>
    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">System Overview</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <CpuCard />
        <QueueCard />
        <CacheCard />
    </div>
</section>
