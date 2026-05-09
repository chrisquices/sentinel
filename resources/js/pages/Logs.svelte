<script lang="ts">
    import {onMount, untrack} from 'svelte';
    import * as Select from '$lib/components/ui/select';
    import * as Card from '$lib/components/ui/card';
    import * as Table from '$lib/components/ui/table';
    import * as Dialog from '$lib/components/ui/dialog';
    import * as Pagination from '$lib/components/ui/pagination';
    import {Badge} from '$lib/components/ui/badge';
    import {Button} from '$lib/components/ui/button';
    import * as ButtonGroup from '$lib/components/ui/button-group';
    import {fetchLogEntries, fetchLogTail, clearLog} from '$lib/api';
    import {Trash2, RefreshCw} from 'lucide-svelte';
    import {type BadgeVariant} from '$lib/components/ui/badge';
    import {AlertTriangle, Bug, Info, AlertCircle, Flame} from 'lucide-svelte';
    import type {ComponentType} from 'svelte';
    import type {LogChannel, LogEntry, LogEntriesResult, LogTailResult, LogInitialData} from '$lib/types';
    import {Skeleton} from '$lib/components/ui/skeleton';

    interface Props {
        initialData?: LogInitialData | null;
    }

    let {initialData}: Props = $props();

    // region --- Channel Selector --------------------------------------------------------------------------------------
    let channels: LogChannel[] = $derived(initialData?.channels ?? []);
    let activeChannel = $state(typeof localStorage !== 'undefined' ? localStorage.getItem('logs:channel') ?? '' : '');

    $effect(() => {
        if (channels.length > 0 && activeChannel === '') {
            activeChannel = channels[0].name;
        }
    });

    $effect(() => {
        if (activeChannel) localStorage.setItem('logs:channel', activeChannel);
    });
    // endregion

    // region --- Level Filter -----------------------------------------------------------------------------------------
    const levels = ['', 'error', 'warning', 'info', 'debug'] as const;
    type Level = typeof levels[number];

    let activeLevel = $state<Level>((typeof localStorage !== 'undefined' ? localStorage.getItem('logs:level') as Level : '') ?? '');

    $effect(() => {
        localStorage.setItem('logs:level', activeLevel);
    });

    const levelVariant: Record<string, BadgeVariant> = {
        emergency: 'destructive',
        alert: 'destructive',
        critical: 'destructive',
        error: 'destructive',
        warning: 'warning',
        notice: 'info',
        info: 'info',
        debug: 'secondary',
    };

    const levelIcon: Record<string, ComponentType> = {
        emergency: Flame,
        alert: Flame,
        critical: Flame,
        error: AlertCircle,
        warning: AlertTriangle,
        notice: Info,
        info: Info,
        debug: Bug,
    };

    const levelClass: Record<string, string> = {
        emergency: 'text-destructive',
        alert: 'text-destructive',
        critical: 'text-destructive',
        error: 'text-destructive',
        warning: 'text-warning',
        notice: 'text-info',
        info: 'text-info',
        debug: 'text-muted-foreground',
    };
    // endregion

    // region --- Logs --------------------------------------------------------------------------------------------------
    let entries = $state<LogEntry[]>([]);
    let tailCursor = $state<number>(0);
    let total = $state<number>(0);
    let loading = $state(false);
    const perPage = 20;
    let page = $state(1);

    $effect(() => {
        if (initialData && entries.length === 0) {
            entries = initialData.entries;
            tailCursor = initialData.tailCursor;
            total = initialData.total;
        }
    });

    $effect(() => {
        const ch = activeChannel;
        const lvl = activeLevel;
        const pg = page;
        if (ch) {
            untrack(() => void loadEntries(ch, pg, lvl));
        }
    });

    async function loadEntries(ch: string, pg: number, lvl: string) {
        if (loading) return;
        loading = true;
        try {
            const result = await fetchLogEntries(ch, pg, perPage, lvl) as LogEntriesResult;
            entries = result.entries;
            total = result.total;
            tailCursor = result.tailCursor;
        } finally {
            loading = false;
        }
    }

    function refresh() {
        void loadEntries(activeChannel, page, activeLevel);
    }

    // endregion

    // region --- Log Dialog --------------------------------------------------------------------------------------------
    let selectedEntry = $state<LogEntry | null>(null);

    // endregion
</script>

<section>
    <h2 class="font-semibold text-foreground mb-4">Logs</h2>

    <Card.Root>
        <Card.Header class="h-auto py-6 flex-col">

            <div class="flex flex-row items-center justify-between w-full">

                <!-- Level Filter -->
                <ButtonGroup.Root>
                    {#each levels as lvl}
                        {@const Icon = lvl === '' ? null : levelIcon[lvl]}
                        <Button
                                onclick={() => { activeLevel = lvl; page = 1; }}
                                variant={activeLevel === lvl ? (levelVariant[lvl] ?? 'default') : 'outline'}
                                class="capitalize"
                        >
                            {#if Icon}
                                <Icon class="size-4 mr-1"/>
                            {/if}
                            {lvl === '' ? 'All' : lvl}
                        </Button>
                    {/each}
                </ButtonGroup.Root>

                <Button variant="secondary" onclick={refresh} disabled={loading}>
                    <RefreshCw class="size-4 {loading ? 'animate-spin' : ''}"/>
                    Refresh
                </Button>
            </div>

            <!-- Channel Selector -->
            <div class="flex flex-wrap gap-2 w-full">
                {#if initialData}
                    {#each channels as channel}
                        <Button
                                onclick={() => { activeChannel = channel.name; page = 1; }}
                                variant={activeChannel === channel.name ? 'default' : 'outline'}
                                class="capitalize"
                        >
                            {channel.name}
                        </Button>
                    {/each}
                {:else}
                    {#each Array(3).fill(0) as _}
                        <Skeleton class="h-9 w-20 rounded-md"/>
                    {/each}
                {/if}
            </div>
        </Card.Header>

        <Card.Content class="p-0">
            <Table.Root>
                <Table.Header>
                    <Table.Row>

                        <!-- Level -->
                        <Table.Head class="rounded-none! w-32 shrink-0">Level</Table.Head>

                        <!-- Timestamp -->
                        <Table.Head class="rounded-none! w-64 shrink-0">Time</Table.Head>

                        <!-- Description -->
                        <Table.Head class="rounded-none!">Message</Table.Head>

                    </Table.Row>
                </Table.Header>
                <Table.Body>
                    {#if initialData}
                        {#if entries.length === 0}
                            <Table.Row>
                                <Table.Cell colspan={4} class="text-center py-8 text-muted-foreground">
                                    {loading ? 'Loading…' : 'No log entries'}
                                </Table.Cell>
                            </Table.Row>
                        {:else}
                            {#each entries as entry}
                                <Table.Row
                                        class="cursor-pointer"
                                        onclick={() => selectedEntry = entry}
                                >
                                    <!-- Level -->
                                    <Table.Cell>
                                        {@const Icon = levelIcon[entry.level.toLowerCase()]}
                                        <div class="flex items-center gap-2 {levelClass[entry.level.toLowerCase()]}">
                                            {#if Icon}
                                                <Icon class="size-5"/>
                                            {/if}
                                            <span class="capitalize">{entry.level}</span>
                                        </div>
                                    </Table.Cell>

                                    <!-- Timestamp -->
                                    <Table.Cell class="whitespace-nowrap">
                                        {entry.timestampFormatted ?? '—'}
                                    </Table.Cell>

                                    <!-- Description -->
                                    <Table.Cell class="whitespace-nowrap">
                                        {entry.message}
                                    </Table.Cell>
                                </Table.Row>
                            {/each}
                        {/if}
                    {:else}
                        {#each Array(5).fill(0) as _}
                            <Table.Row>

                                <!-- Level -->
                                <Table.Cell>
                                    <Skeleton class="h-4 w-16"/>
                                </Table.Cell>

                                <!-- Timestamp -->
                                <Table.Cell>
                                    <Skeleton class="h-4 w-32"/>
                                </Table.Cell>

                                <!-- Message -->
                                <Table.Cell>
                                    <Skeleton class="h-4 w-full"/>
                                </Table.Cell>
                            </Table.Row>
                        {/each}
                    {/if}
                </Table.Body>
            </Table.Root>
        </Card.Content>

        {#if total > perPage}
            <Card.Footer class="justify-center border-t">
                <Pagination.Root count={total} {perPage} bind:page>
                    {#snippet children({pages, currentPage})}
                        <Pagination.Content>
                            <Pagination.Item>
                                <Pagination.PrevButton/>
                            </Pagination.Item>
                            {#each pages as pg (pg.key)}
                                <Pagination.Item>
                                    {#if pg.type === 'ellipsis'}
                                        <Pagination.Ellipsis/>
                                    {:else}
                                        <Pagination.Link page={pg} isActive={currentPage === pg.value}/>
                                    {/if}
                                </Pagination.Item>
                            {/each}
                            <Pagination.Item>
                                <Pagination.NextButton/>
                            </Pagination.Item>
                        </Pagination.Content>
                    {/snippet}
                </Pagination.Root>
            </Card.Footer>
        {/if}
    </Card.Root>
</section>

<Dialog.Root open={selectedEntry !== null} onOpenChange={(open) => { if (!open) selectedEntry = null; }}>
    <Dialog.Content class="sm:max-w-8xl">
        <Dialog.Header>
            <Dialog.Title class="flex items-center gap-4">
                {#if selectedEntry}
                    {@const Icon = levelIcon[selectedEntry.level.toLowerCase()]}
                    <span class="flex items-center gap-2 {levelClass[selectedEntry.level.toLowerCase()]} capitalize">
                        {#if Icon}<Icon class="size-5"/>{/if}
                        {selectedEntry.level}
                    </span>

                    {#if selectedEntry?.timestampFormatted}
                        <span>{selectedEntry.timestampFormatted}</span>
                    {/if}
                {/if}
            </Dialog.Title>
            {#if selectedEntry?.message}
                <Dialog.Description>
                    {selectedEntry.message}
                </Dialog.Description>
            {/if}
        </Dialog.Header>
        <Dialog.Body>
            {#if selectedEntry && selectedEntry.extra}
                <pre class="overflow-x-auto whitespace-pre-wrap break-words">{selectedEntry.extra}</pre>
            {:else}
                <pre class="text-center py-12">No message available.</pre>
            {/if}
        </Dialog.Body>
    </Dialog.Content>
</Dialog.Root>