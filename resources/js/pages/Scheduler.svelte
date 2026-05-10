<script lang="ts">
    import {onMount} from 'svelte';
    import * as Card from '$lib/components/ui/card';
    import * as Table from '$lib/components/ui/table';
    import {Badge} from '$lib/components/ui/badge';
    import type {SchedulerInitialData, SchedulerData, SchedulerTask} from '$lib/types';
    import {fetchScheduler} from '$lib/api';
    import {CalendarClock} from 'lucide-svelte';

    interface Props {
        initialData?: SchedulerInitialData | null;
    }

    let {initialData = null}: Props = $props();

    // region --- Scheduler --------------------------------------------------------------------------------------------
    let tasks = $state<SchedulerTask[]>(initialData?.events ?? []);

    let now = $state(Date.now());

    function formatCountdown(isoString: string): string {
        const diff = Math.floor((new Date(isoString).getTime() - now) / 1000);
        if (diff <= 0) return 'overdue';
        if (diff < 60) return `${diff}s`;
        if (diff < 3600) {
            const m = Math.floor(diff / 60);
            const s = diff % 60;
            return `${m}m ${s}s`;
        }
        const h = Math.floor(diff / 3600);
        const m = Math.floor((diff % 3600) / 60);
        return `${h}h ${m}m`;
    }

    function formatRelative(isoString: string | null): string {
        if (!isoString) return '—';
        const diff = Math.floor((now - new Date(isoString).getTime()) / 1000);
        if (diff < 60) return 'just now';
        if (diff < 3600) return `${Math.floor(diff / 60)}m ago`;
        if (diff < 86400) return `${Math.floor(diff / 3600)}h ago`;
        return `${Math.floor(diff / 86400)}d ago`;
    }

    function isOverdue(isoString: string): boolean {
        return new Date(isoString).getTime() <= now;
    }

    onMount(() => {
        const clockInterval = setInterval(() => {
            now = Date.now();
        }, 1000);

        const pollInterval = setInterval(async () => {
            const res = await fetchScheduler() as SchedulerData;
            tasks = res.events ?? [];
        }, 10000);

        return () => {
            clearInterval(clockInterval);
            clearInterval(pollInterval);
        };
    });
    // endregion
</script>

<section class="3xl:h-full 3xl:flex 3xl:flex-col">

    <h2 class="font-semibold text-foreground mb-4 flex items-center gap-2 3xl:shrink-0">
        <CalendarClock class="size-4"/>
        Scheduler
    </h2>

    <Card.Root class="3xl:flex-1 3xl:flex 3xl:flex-col 3xl:min-h-0 3xl:overflow-hidden">
        <Card.Content class="p-0 overflow-hidden 3xl:flex-1">
            <div class="overflow-y-auto max-h-80 3xl:max-h-none 3xl:h-full [&_[data-slot='table-container']]:h-full">
                <Table.Root class={tasks.length ? 'h-full' : ''}>
                    <Table.Header>
                        <Table.Row>
                            <Table.Head class="rounded-none!">Command</Table.Head>
                            <Table.Head class="rounded-none!">Schedule</Table.Head>
                            <Table.Head class="rounded-none!">Next Run</Table.Head>
                            <Table.Head class="rounded-none!">Last Run</Table.Head>
                            <Table.Head class="rounded-none! text-right">Status</Table.Head>
                        </Table.Row>
                    </Table.Header>
                    <Table.Body>
                        {#if initialData}
                            {#if tasks.length === 0}
                                <Table.Row>
                                    <Table.Cell colspan={5} class="text-center py-8 text-muted-foreground">
                                        No scheduled tasks
                                    </Table.Cell>
                                </Table.Row>
                            {:else}
                                {#each tasks as task}
                                    <Table.Row>
                                        <Table.Cell>
                                            <div class="flex items-center gap-2">
                                                <CalendarClock class="w-3.5 h-3.5 text-muted-foreground shrink-0"/>
                                                <span class="font-medium text-card-foreground font-mono ">{task.command}</span>
                                            </div>
                                        </Table.Cell>

                                        <Table.Cell>
                                            <span class="text-card-foreground">{task.expressionLabel}</span>
                                            <span class="block text-muted-foreground font-mono ">{task.expression}</span>
                                        </Table.Cell>

                                        <Table.Cell>
                                        <span class={isOverdue(task.nextRun) ? 'text-amber-500 font-medium' : 'text-card-foreground'}>
                                            {formatCountdown(task.nextRun)}
                                        </span>
                                        </Table.Cell>

                                        <Table.Cell class="text-muted-foreground">
                                            {formatRelative(task.lastRanAt)}
                                        </Table.Cell>

                                        <Table.Cell class="text-right">
                                            {#if task.status === 'success'}
                                                <Badge variant="outline"
                                                       class="border-green-500/30 bg-green-500/10 text-green-600 dark:text-green-400">
                                                    success
                                                </Badge>
                                            {:else if task.status === 'failed'}
                                                <Badge variant="destructive">failed</Badge>
                                            {:else}
                                                <Badge variant="secondary">never run</Badge>
                                            {/if}
                                        </Table.Cell>
                                    </Table.Row>
                                {/each}
                            {/if}
                        {/if}
                    </Table.Body>
                </Table.Root>
            </div>
        </Card.Content>
    </Card.Root>
</section>
