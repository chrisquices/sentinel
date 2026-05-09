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
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

        <div class="flex items-center gap-2">
            <Button variant={activeTab === 'system' ? 'default' : 'secondary'} onclick={() => activeTab = 'system'}>
                <Monitor class="size-4"/>
                System
            </Button>

            <Button variant={activeTab === 'scheduler' ? 'default' : 'secondary'} onclick={() => activeTab = 'scheduler'}>
                <CalendarClock class="size-4"/>
                Scheduler
            </Button>

            <Button variant={activeTab === 'queue' ? 'default' : 'secondary'} onclick={() => activeTab = 'queue'}>
                <ListTodo class="size-4"/>
                Queue
            </Button>

            <Button variant={activeTab === 'logs' ? 'default' : 'secondary'} onclick={() => activeTab = 'logs'}>
                <ScrollText class="size-4"/>
                Logs
            </Button>
        </div>

        {#if activeTab === 'system'}
            <div class="grid grid-cols-1 lg:grid-cols-6 gap-6 items-stretch">
                <System initialData={systemData} class="lg:col-span-2 h-full"/>
                <Runtime initialData={runtimeData} class="lg:col-span-4 h-full"/>
            </div>
        {/if}
        {#if activeTab === 'scheduler'}
            <Scheduler initialData={schedulerData}/>
        {/if}
        {#if activeTab === 'queue'}
            <Queue initialData={queueData}/>
        {/if}
        {#if activeTab === 'logs'}
            <Logs initialData={logsData}/>
        {/if}

    </main>
</div>
