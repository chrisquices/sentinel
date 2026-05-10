<script lang="ts">
    import {onMount} from 'svelte';
    import * as Card from '$lib/components/ui/card';
    import * as Table from '$lib/components/ui/table';
    import * as Dialog from '$lib/components/ui/dialog';
    import type {QueueData, QueueInitialData, Job, JobPayload} from '$lib/types';
    import {Clock, Loader, CheckCheck, AlertCircle, ListTodo} from 'lucide-svelte';
    import * as ButtonGroup from '$lib/components/ui/button-group';
    import {Button} from '$lib/components/ui/button';
    import {RotateCcw, Trash2} from 'lucide-svelte';
    import {
        fetchQueue,
        fetchJobPayload,
        clearCompletedJobs,
        clearFailedJobs,
        retryFailedJob,
        deleteCompletedJob,
        deleteFailedJob
    } from "$lib/api";

    interface Props {
        initialData?: QueueInitialData | null;
    }

    let {initialData = null}: Props = $props();

    // region --- Queue ------------------------------------------------------------------------------------------------
    const filters = ['pending', 'processing', 'completed', 'failed'] as const;
    type Filter = typeof filters[number];

    let queueData = $state<QueueData | null>(initialData);
    let activeFilter = $state<Filter>(
        (typeof localStorage !== 'undefined' ? localStorage.getItem('queue:filter') as Filter : null) ?? 'pending'
    );

    $effect(() => {
        localStorage.setItem('queue:filter', activeFilter);
    });

    let filteredJobs = $derived(
        queueData?.jobs.filter(j => j.status === activeFilter) ?? []
    );

    onMount(() => {
        const interval = setInterval(async () => {
            queueData = await fetchQueue() as QueueData;
        }, 3000);

        return () => clearInterval(interval);
    });
    // endregion

    // region --- Job Detail --------------------------------------------------------------------------------------------
    let selectedJob = $state<Job | null>(null);
    let jobPayload = $state<JobPayload | null>(null);
    let loadingPayload = $state(false);

    async function openJobDetail(job: Job) {
        selectedJob = job;
        jobPayload = null;
        loadingPayload = true;
        try {
            jobPayload = await fetchJobPayload(job.id) as JobPayload;
        } finally {
            loadingPayload = false;
        }
    }

    const sourceLabel: Record<string, string> = {
        pending: 'Pending',
        processing: 'Processing',
        completed: 'Completed',
        failed: 'Failed',
    };

    const sourceClass: Record<string, string> = {
        pending: 'text-muted-foreground',
        processing: 'text-blue-500',
        completed: 'text-green-600',
        failed: 'text-destructive',
    };
    // endregion

    // region --- Delete Dialog ----------------------------------------------------------------------------------------
    let deleteTarget = $state<'completed' | 'failed' | null>(null);
    let isDeleting = $state(false);

    async function confirmDelete() {
        isDeleting = true;
        try {
            if (deleteTarget === 'completed') await clearCompletedJobs();
            else if (deleteTarget === 'failed') await clearFailedJobs();
        } finally {
            setTimeout(() => {
                deleteTarget = null;
                isDeleting = false;
            }, 1000);
        }
    }
    // endregion
</script>

<section class="3xl:h-full 3xl:flex 3xl:flex-col">

    <h2 class="font-semibold text-foreground mb-4 flex items-center gap-2 3xl:shrink-0">
        <ListTodo class="size-4"/>
        Queue
    </h2>

    <Card.Root class="3xl:flex-1 3xl:flex 3xl:flex-col 3xl:min-h-0 3xl:overflow-hidden">
        <Card.Header>
            <!-- Filter Button Group -->
            <ButtonGroup.Root>
                {#each filters as filter}
                    <Button
                            onclick={() => activeFilter = filter}
                            variant={activeFilter === filter ? 'default' : 'outline'}
                    >
                        {#if filter === 'pending'}
                            <Clock class="size-4"/>
                        {:else if filter === 'processing'}
                            <Loader class="size-4 {(queueData?.summary.processing ?? 0) > 0 ? 'animate-spin' : ''}"/>
                        {:else if filter === 'completed'}
                            <CheckCheck class="size-4"/>
                        {:else}
                            <AlertCircle class="size-4"/>
                        {/if}
                        {filter.charAt(0).toUpperCase() + filter.slice(1)}
                        <span class="ml-1 opacity-60">{queueData?.summary[filter] ?? 0}</span>
                    </Button>
                {/each}
            </ButtonGroup.Root>

            <!-- Filter Button Group -->
            <ButtonGroup.Root class="ml-auto">

                <!-- Delete Completed Jobs -->
                {#if activeFilter === 'completed'}
                    <Button onclick={() => deleteTarget = 'completed'} variant="secondary">
                        <Trash2 class="size-4"/>
                        Delete Completed Queue
                    </Button>
                {/if}

                <!-- Delete Failed Jobs -->
                {#if activeFilter === 'failed'}
                    <Button onclick={() => deleteTarget = 'failed'} variant="secondary">
                        <Trash2 class="size-4"/>
                        Delete Failed Queue
                    </Button>
                {/if}

            </ButtonGroup.Root>
        </Card.Header>

        <Card.Content class="p-0 overflow-hidden 3xl:flex-1">
            <div class="overflow-y-auto max-h-80 3xl:max-h-none 3xl:h-full [&_[data-slot='table-container']]:h-full">

                <!-- Jobs -->
                <Table.Root class={filteredJobs.length ? 'h-full' : ''}>
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
                        {#if initialData}
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
                                    <Table.Row class="cursor-pointer" onclick={() => openJobDetail(job)}>

                                        <!-- Job -->
                                        <Table.Cell>{job.displayName}</Table.Cell>

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
                                                            onclick={(e) => { e.stopPropagation(); deleteCompletedJob(job.id); }}>
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
                                                            onclick={(e) => { e.stopPropagation(); retryFailedJob(job.id); }}>
                                                        <RotateCcw/>
                                                    </Button>

                                                    <Button variant="secondary" size="icon"
                                                            onclick={(e) => { e.stopPropagation(); deleteFailedJob(job.id); }}>
                                                        <Trash2/>
                                                    </Button>
                                                </div>
                                            </Table.Cell>
                                        {/if}
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

<Dialog.Root open={selectedJob !== null}
             onOpenChange={(open) => { if (!open) { selectedJob = null; jobPayload = null; } }}>
    <Dialog.Content class="sm:max-w-3xl">
        <Dialog.Header>
            <Dialog.Title class="flex items-center gap-3">
                {#if jobPayload}
                    <span class={sourceClass[jobPayload.source]}>{sourceLabel[jobPayload.source]}</span>
                    <span class="text-foreground">{jobPayload.displayName}</span>
                {:else}
                    <span>{selectedJob?.displayName ?? ''}</span>
                {/if}
            </Dialog.Title>
            {#if jobPayload}
                <Dialog.Description>{jobPayload.jobClass}</Dialog.Description>
            {/if}
        </Dialog.Header>
        <Dialog.Body>
            {#if loadingPayload}
                <div class="flex items-center justify-center py-12">
                    <Loader class="size-5 animate-spin text-muted-foreground"/>
                </div>
            {:else if jobPayload}
                <div class="flex flex-col gap-6">

                    <!-- Metadata -->
                    <dl class="grid grid-cols-2 gap-x-8 gap-y-2 text-sm">
                        <dt class="text-muted-foreground">Queue</dt>
                        <dd>{jobPayload.queue}</dd>

                        <dt class="text-muted-foreground">Connection</dt>
                        <dd>{jobPayload.connection ?? '—'}</dd>

                        <dt class="text-muted-foreground">Attempts</dt>
                        <dd>{jobPayload.attempts}</dd>

                        {#if jobPayload.runTime != null}
                            <dt class="text-muted-foreground">Run Time</dt>
                            <dd>{jobPayload.runTime}ms</dd>
                        {/if}

                        {#if jobPayload.completedAt}
                            <dt class="text-muted-foreground">Completed At</dt>
                            <dd>{jobPayload.completedAt}</dd>
                        {/if}

                        {#if jobPayload.failedAt}
                            <dt class="text-muted-foreground">Failed At</dt>
                            <dd>{jobPayload.failedAt}</dd>
                        {/if}
                    </dl>

                    <!-- Exception -->
                    {#if jobPayload.exception}
                        <div>
                            <p class="text-sm font-medium text-muted-foreground mb-2">Exception</p>
                            <pre class="overflow-x-auto whitespace-pre-wrap break-words text-xs text-destructive">{jobPayload.exception}</pre>
                        </div>
                    {/if}

                    <!-- Raw payload -->
                    {#if jobPayload.payload}
                        <div>
                            <p class="text-sm font-medium text-muted-foreground mb-2">Payload</p>
                            <pre class="overflow-x-auto whitespace-pre-wrap break-words text-xs">{JSON.stringify(jobPayload.payload, null, 2)}</pre>
                        </div>
                    {/if}

                </div>
            {/if}
        </Dialog.Body>
    </Dialog.Content>
</Dialog.Root>

<Dialog.Root open={deleteTarget !== null} onOpenChange={(open) => { if (!open && !isDeleting) deleteTarget = null; }}>
    <Dialog.Content withLoading>
        <Dialog.Header>
            <Dialog.Title>Delete all {deleteTarget} jobs?</Dialog.Title>
            <Dialog.Description>This action cannot be undone.</Dialog.Description>
        </Dialog.Header>
        <Dialog.Footer>
            <Button variant="destructive" disabled={isDeleting} onclick={confirmDelete}>Delete</Button>
        </Dialog.Footer>
    </Dialog.Content>
</Dialog.Root>