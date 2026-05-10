<script lang="ts">
    import {onMount} from 'svelte';
    import {Toaster} from 'svelte-sonner';
    import Topbar from '$lib/components/ui/Topbar.svelte';
    import System from './pages/System.svelte';
    import Runtime from './pages/Runtime.svelte';
    import Queue from './pages/Queue.svelte';
    import Scheduler from './pages/Scheduler.svelte';
    import Logs from './pages/Logs.svelte';
    import type {
        SystemInitialData,
        RuntimeData,
        SchedulerInitialData,
        QueueInitialData,
        LogInitialData
    } from '$lib/types';
    import {fetchSystem, fetchRuntime, fetchScheduler, fetchQueue, fetchLogs} from '$lib/api';

    interface Props {
        projectName?: string;
    }

    let {projectName = 'My Project'}: Props = $props();

    let ready = $state(false);
    let systemData = $state<SystemInitialData | null>(null);
    let runtimeData = $state<RuntimeData | null>(null);
    let schedulerData = $state<SchedulerInitialData | null>(null);
    let queueData = $state<QueueInitialData | null>(null);
    let logsData = $state<LogInitialData | null>(null);

    onMount(async () => {
        const [sys, rt, sched, queue, logs] = await Promise.all([
            fetchSystem(),
            fetchRuntime(),
            fetchScheduler(),
            fetchQueue(),
            fetchLogs(),
        ]);
        systemData   = sys   as SystemInitialData;
        runtimeData  = rt    as RuntimeData;
        schedulerData = sched as SchedulerInitialData;
        queueData    = queue as QueueInitialData;
        logsData     = logs  as LogInitialData;
        ready = true;
    });
</script>

<Toaster
    theme="dark"
    position="top-right"
    expand
    visibleToasts={3}
    richColors
    duration={3000}
    toastOptions={{ class: '!w-108 right-0', descriptionClass: '!w-108' }}
/>

{#if ready}
<div class="h-screen w-full flex flex-col overflow-hidden transition-colors duration-200 bg-background">
    <Topbar {projectName}/>
    <div class="flex-1 flex flex-col 2xl:flex-row overflow-hidden">

        <!-- Left panel -->
        <div class="flex-1 overflow-y-auto p-6 space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-6 gap-6 items-stretch">
                <System initialData={systemData} class="lg:col-span-2 h-full"/>
                <Runtime initialData={runtimeData} class="lg:col-span-4 h-full"/>
            </div>
            <Scheduler initialData={schedulerData}/>
            <Queue initialData={queueData}/>
        </div>

        <!-- Right panel: Logs -->
        <div class="2xl:w-1/2 2xl:border-l flex flex-col overflow-hidden p-6">
            <Logs initialData={logsData}/>
        </div>

    </div>
</div>
{/if}
