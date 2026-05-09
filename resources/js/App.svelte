<script lang="ts">
    import {onMount} from 'svelte';
    import Topbar from '$lib/components/ui/Topbar.svelte';
    import System from './pages/System.svelte';
    import Runtime from './pages/Runtime.svelte';
    import Queue from './pages/Queue.svelte';
    import Scheduler from './pages/Scheduler.svelte';
    import Logs from './pages/Logs.svelte';
    import {fetchSystem, fetchRuntime, fetchQueue, fetchScheduler} from '$lib/api';
    import type {SystemInitialData, RuntimeInfo, SchedulerInitialData, QueueInitialData, LogInitialData} from '$lib/types';

    interface Props {
        projectName?: string;
    }

    let {projectName = 'My Project'}: Props = $props();

    // region --- Theme ------------------------------------------------------------------------------------------------
    let isDark = $state(false);

    function toggleTheme(): void {
        isDark = !isDark;
        document.documentElement.classList.toggle('dark', isDark);
        localStorage.setItem('vulcan-sentinel-theme', isDark ? 'dark' : 'light');
    }
    // endregion

    // region --- Initial Data -----------------------------------------------------------------------------------------
    const sentinel = window.__vulcanSentinel;

    let systemData    = $state<SystemInitialData | null>(sentinel?.systemData ?? null);
    let runtimeData   = $state<RuntimeInfo | null>(sentinel?.runtimeData ?? null);
    let schedulerData = $state<SchedulerInitialData | null>(sentinel?.schedulerData ?? null);
    let queueData     = $state<QueueInitialData | null>(sentinel?.queueData ?? null);
    let logsData      = $state<LogInitialData | null>(sentinel?.logsData ?? null);

    onMount(async () => {
        const saved = localStorage.getItem('vulcan-sentinel-theme');
        if (saved === 'dark' || (!saved && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            isDark = true;
            document.documentElement.classList.add('dark');
        }

        const [sysResult, runtimeResult, schedulerResult, queueResult] = await Promise.allSettled([
            fetchSystem(),
            fetchRuntime(),
            fetchScheduler(),
            fetchQueue(),
        ]);

        if (sysResult.status === 'fulfilled') systemData = sysResult.value as SystemInitialData;
        if (runtimeResult.status === 'fulfilled') runtimeData = runtimeResult.value as RuntimeInfo;
        if (schedulerResult.status === 'fulfilled') schedulerData = schedulerResult.value as SchedulerInitialData;
        if (queueResult.status === 'fulfilled') queueData = queueResult.value as QueueInitialData;
    });
    // endregion
</script>

<div class="min-h-screen transition-colors duration-200 bg-background">
    <Topbar {isDark} {toggleTheme} {projectName} />
    <main class="max-w-8xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-10">
        <div class="grid grid-cols-1 lg:grid-cols-6 gap-6 items-stretch">
            <System initialData={systemData} class="lg:col-span-2 h-full" />
            <Runtime initialData={runtimeData} class="lg:col-span-4 h-full" />
        </div>
        <Scheduler initialData={schedulerData} />
        <Queue initialData={queueData} />

        <Logs initialData={logsData} />
    </main>
</div>
