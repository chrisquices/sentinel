<script lang="ts">
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
    import {Button} from "$lib/components/ui/button";
    import {Monitor, CalendarClock, ListTodo, ScrollText} from "lucide-svelte";

    interface Props {
        projectName?: string;
    }

    let {projectName = 'My Project'}: Props = $props();

    // region --- Initial Data -----------------------------------------------------------------------------------------
    const sentinel = window.__sentinel;

    let systemData = $state<SystemInitialData | null>(sentinel?.systemData ?? null);
    let runtimeData = $state<RuntimeData | null>(sentinel?.runtimeData ?? null);
    let schedulerData = $state<SchedulerInitialData | null>(sentinel?.schedulerData ?? null);
    let queueData = $state<QueueInitialData | null>(sentinel?.queueData ?? null);
    let logsData = $state<LogInitialData | null>(sentinel?.logsData ?? null);
    // endregion

    // region --- Active Tab -------------------------------------------------------------------------------------------
    const tabs = ['system', 'scheduler', 'queue', 'logs'] as const;
    type Tab = typeof tabs[number];

    let activeTab = $state<Tab>(
        (typeof localStorage !== 'undefined' ? localStorage.getItem('app:tab') as Tab : null) ?? 'scheduler'
    );

    $effect(() => {
        localStorage.setItem('app:tab', activeTab);
    });
    // endregion
</script>

<div class="min-h-screen transition-colors duration-200 bg-background">
    <Topbar {projectName}/>
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
        <div class="grid grid-cols-1 lg:grid-cols-6 gap-6 items-stretch">
            <System initialData={systemData} class="lg:col-span-2 h-full"/>
            <Runtime initialData={runtimeData} class="lg:col-span-4 h-full"/>
        </div>
        <Scheduler initialData={schedulerData}/>

        <Queue initialData={queueData}/>

        <Logs initialData={logsData}/>
    </main>
</div>
