<script lang="ts">
    import {onMount} from 'svelte';
    import * as Card from '$lib/components/ui/card';
    import * as Table from '$lib/components/ui/table';
    import type {QueueData, Job} from '$lib/types';
    import {Clock, Loader, CheckCheck, AlertCircle} from 'lucide-svelte';
    import * as ButtonGroup from '$lib/components/ui/button-group';
    import {Button} from '$lib/components/ui/button';
    import {RotateCcw, Trash2} from 'lucide-svelte';
    import {
        clearCompletedJobs,
        clearFailedJobs,
        retryFailedJob,
        deleteCompletedJob,
        deleteFailedJob
    } from "$lib/api";

    interface Props {
        initialData?: QueueData | null;
    }

    let {initialData = null}: Props = $props();

    const path = (window as any).__vulcanSentinel?.basePath ?? 'vulcan-sentinel';

    const filters = ['pending', 'processing', 'completed', 'failed'] as const;
    type Filter = typeof filters[number];

    let queueData: QueueData | null = $state(initialData);
    let activeFilter = $state<Filter>('pending');

    let filteredJobs = $derived(
        queueData?.jobs.filter(j => j.status === activeFilter) ?? []
    );

    onMount(() => {
        const interval = setInterval(async () => {
            const res = await fetch(`/${path}/api/queue`);
            queueData = await res.json();
        }, 1000);

        return () => clearInterval(interval);
    });
</script>

<section>

    <!-- Queue -->
    <h2 class="font-semibold text-foreground mb-4">Queue</h2>

    <!-- Jobs -->
    <Card.Root>
        <Card.Header>
            <!-- Filter Button Group -->
            <ButtonGroup.Root>
                {#each filters as filter}
                    <Button
                            onclick={() => activeFilter = filter}
                            variant={activeFilter === filter ? 'default' : 'outline'}
                    >
                        {#if filter === 'pending'}
                            <Clock class="size-3.5"/>
                        {:else if filter === 'processing'}
                            <Loader class="size-3.5 {(queueData?.summary.processing ?? 0) > 0 ? 'animate-spin' : ''}"/>
                        {:else if filter === 'completed'}
                            <CheckCheck class="size-3.5"/>
                        {:else}
                            <AlertCircle class="size-3.5"/>
                        {/if}
                        {filter.charAt(0).toUpperCase() + filter.slice(1)}
                        <span class="ml-1 opacity-60">{queueData?.summary[filter] ?? 0}</span>
                    </Button>
                {/each}
            </ButtonGroup.Root>

            <!-- Filter Button Group -->
            <ButtonGroup.Root class="ml-auto">

                <!-- Clear Completed Jobs -->
                {#if activeFilter === 'completed'}
                    <Button onclick={() => clearCompletedJobs()} variant="secondary">
                        <Trash2 class="size-3.5"/>
                        Clear Completed Queue
                    </Button>
                {/if}

                <!-- Clear Failed Jobs -->
                {#if activeFilter === 'failed'}
                    <Button onclick={() => clearFailedJobs()} variant="secondary">
                        <Trash2 class="size-3.5"/>
                        Clear Failed Queue
                    </Button>
                {/if}

            </ButtonGroup.Root>
        </Card.Header>

        <Card.Content class="p-0">

            <!-- Jobs -->
            <Table.Root>
                <Table.Header>
                    <Table.Row>
                        <!-- Job -->
                        <Table.Head class="rounded-none!">Job</Table.Head>

                        <!-- Queue -->
                        <Table.Head class="rounded-none!">Queue</Table.Head>

                        <!-- Attempts -->
                        <Table.Head class="rounded-none!">Attempts</Table.Head>

                        <!-- Run Time -->
                        {#if activeFilter === 'completed'}
                            <Table.Head class="rounded-none!">Run Time</Table.Head>
                        {/if}

                        <!-- Exception -->
                        {#if activeFilter === 'failed'}
                            <Table.Head class="rounded-none!">Exception</Table.Head>
                        {/if}

                        <!-- Timestamp (Created At / Completed At / Failed At) -->
                        <Table.Head class="rounded-none! text-right">
                            {activeFilter === 'failed' ? 'Failed At' : activeFilter === 'completed' ? 'Completed At' : 'Created At'}
                        </Table.Head>

                        <!-- Actions -->
                        {#if activeFilter === 'completed' || activeFilter === 'failed'}
                            <Table.Head class="rounded-none! text-right">Actions</Table.Head>
                        {/if}
                    </Table.Row>
                </Table.Header>
                <Table.Body>
                    {#if filteredJobs.length === 0}

                        <!-- No results found -->
                        <Table.Row>
                            <Table.Cell colspan={99}
                                        class="text-center py-8 text-muted-foreground">
                                No {activeFilter} jobs
                            </Table.Cell>
                        </Table.Row>
                    {:else}
                        {#each filteredJobs as job}
                            <Table.Row>

                                <!-- Job -->
                                <Table.Cell>
                                    <span class="text-card-foreground font-medium">{job.displayName}</span>
                                    <span class="block text-muted text-sm">{job.jobClass}</span>
                                </Table.Cell>

                                <!-- Queue -->
                                <Table.Cell>{job.queue}</Table.Cell>

                                <!-- Attempts -->
                                <Table.Cell>{job.attempts}</Table.Cell>

                                <!-- Pending -->
                                {#if activeFilter === 'pending'}

                                    <!-- Timestamp (Created At / Completed At / Failed At) -->
                                    <Table.Cell class="text-right">{job.createdAtFormatted}</Table.Cell>
                                {/if}

                                <!-- Processing -->
                                {#if activeFilter === 'processing'}

                                    <!-- Timestamp (Created At / Completed At / Failed At) -->
                                    <Table.Cell class="text-right">{job.createdAtFormatted}</Table.Cell>
                                {/if}

                                <!-- Completed -->
                                {#if activeFilter === 'completed'}

                                    <!-- Run Time -->
                                    <Table.Cell>{job.runTimeFormatted ?? '—'}</Table.Cell>

                                    <!-- Timestamp -->
                                    <Table.Cell class="text-right">{job.createdAtFormatted}</Table.Cell>

                                    <!-- Actions -->
                                    <Table.Cell class="text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <Button variant="secondary" size="icon"
                                                    onclick={() => deleteCompletedJob(job.id)}>
                                                <Trash2/>
                                            </Button>
                                        </div>
                                    </Table.Cell>
                                {/if}

                                <!-- Failed -->
                                {#if activeFilter === 'failed'}

                                    <!-- Exception -->
                                    <Table.Cell class="max-w-xs truncate"
                                                title={job.exceptionFull}>{job.exception}</Table.Cell>

                                    <!-- Timestamp -->
                                    <Table.Cell class="text-right">{job.createdAtFormatted}</Table.Cell>

                                    <!-- Actions -->
                                    <Table.Cell class="text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <Button variant="secondary" size="icon"
                                                    onclick={() => retryFailedJob(job.id)}>
                                                <RotateCcw/>
                                            </Button>

                                            <Button variant="secondary" size="icon"
                                                    onclick={() => deleteFailedJob(job.id)}>
                                                <Trash2/>
                                            </Button>
                                        </div>
                                    </Table.Cell>
                                {/if}
                            </Table.Row>
                        {/each}
                    {/if}
                </Table.Body>
            </Table.Root>
        </Card.Content>
    </Card.Root>
</section>