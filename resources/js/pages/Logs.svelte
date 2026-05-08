<script lang="ts">
    import {onMount} from 'svelte';
    import * as Select from '$lib/components/ui/select';
    import * as Card from '$lib/components/ui/card';
    import * as Table from '$lib/components/ui/table';
    import * as Dialog from '$lib/components/ui/dialog';
    import {Badge} from '$lib/components/ui/badge';
    import {Button} from '$lib/components/ui/button';
    import * as ButtonGroup from '$lib/components/ui/button-group';
    import type {LogChannel, LogEntry, LogEntriesResult, LogTailResult} from '$lib/types';
    import {fetchLogEntries, fetchLogTail, clearLog} from '$lib/api';
    import {Trash2} from 'lucide-svelte';
    import { type BadgeVariant } from '$lib/components/ui/badge';

    interface Props {
        initialChannels?: LogChannel[] | null;
    }

    let {initialChannels = null}: Props = $props();

    const levels = ['', 'error', 'warning', 'info', 'debug'] as const;
    type Level = typeof levels[number];

    let channels: LogChannel[] = $derived(initialChannels ?? []);
    let activeChannel = $state('');
    let activeLevel = $state<Level>('');
    let entries = $state<LogEntry[]>([]);
    let cursor = $state<number | null>(null);
    let hasMore = $state(false);
    let tailCursor = $state(0);
    let loading = $state(false);
    let clearing = $state(false);
    let selectedEntry = $state<LogEntry | null>(null);

    $effect(() => {
        if (channels.length > 0 && activeChannel === '') {
            activeChannel = channels[0].name;
        }
    });

    $effect(() => {
        const ch = activeChannel;
        const lvl = activeLevel;
        if (ch) {
            void load(ch, lvl, null, true);
        }
    });

    async function load(ch: string, lvl: string, cur: number | null, reset: boolean) {
        if (loading) return;
        loading = true;
        try {
            const result = await fetchLogEntries(ch, cur, lvl) as LogEntriesResult;
            entries    = reset ? result.entries : [...entries, ...result.entries];
            cursor     = result.cursor;
            hasMore    = result.hasMore;
            tailCursor = result.tailCursor;
        } finally {
            loading = false;
        }
    }

    async function handleClear() {
        if (!confirm(`Clear all log entries for "${activeChannel}"?`)) return;
        clearing = true;
        try {
            await clearLog(activeChannel);
            entries    = [];
            cursor     = null;
            hasMore    = false;
            tailCursor = 0;
        } finally {
            clearing = false;
        }
    }

    onMount(() => {
        const interval = setInterval(async () => {
            if (!activeChannel || tailCursor === 0) return;
            const result = await fetchLogTail(activeChannel, tailCursor, activeLevel) as LogTailResult;
            if (result.entries.length > 0) {
                entries = [...result.entries, ...entries];
            }
            tailCursor = result.tailCursor;
        }, 5000);

        return () => clearInterval(interval);
    });


    import { AlertTriangle, Bug, Info, AlertCircle, Flame } from 'lucide-svelte';
    import type { ComponentType } from 'svelte';

    const levelVariant: Record<string, BadgeVariant> = {
        emergency: 'destructive',
        alert: 'destructive',
        critical: 'destructive',
        error: 'destructive',
        warning: 'warning',
        notice: 'info',
        info: 'info',
        debug: 'ghost',
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
</script>

<section>
    <h2 class="font-semibold text-foreground mb-4">Logs</h2>

    <Card.Root>
        <Card.Header>

            <!-- Channel Selector -->
            {#if channels.length > 0}
                <Select.Root type="single" bind:value={activeChannel}>
                    <Select.Trigger class="w-56">
                        {activeChannel ? `Log: ${activeChannel}` : 'Select channel'}
                    </Select.Trigger>
                    <Select.Content>
                        {#each channels as ch}
                            <Select.Item value={ch.name}>Log: {ch.name}</Select.Item>
                        {/each}
                    </Select.Content>
                </Select.Root>
            {/if}

            <!-- Level Filter -->
            <ButtonGroup.Root>
                {#each levels as lvl}
                    {@const Icon = lvl === '' ? null : levelIcon[lvl]}
                    <Button
                            onclick={() => activeLevel = lvl}
                            variant={activeLevel === lvl ? (levelVariant[lvl] ?? 'default') : 'outline'}
                            class="capitalize"
                    >
                        {#if Icon}<Icon class="size-5" />{/if}
                        {lvl === '' ? 'All' : lvl}
                    </Button>
                {/each}
            </ButtonGroup.Root>

            <!-- Clear Button -->
            <Button onclick={handleClear} variant="secondary" class="ml-auto" disabled={clearing || !activeChannel}>
                <Trash2 class="size-3.5"/>
                Clear
            </Button>
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
                    {#if entries.length === 0}
                        <Table.Row>
                            <Table.Cell colspan={4} class="text-center py-8 text-muted-foreground">
                                {loading ? 'Loading…' : 'No log entries'}
                            </Table.Cell>
                        </Table.Row>
                    {:else}
                        {#each entries as entry, i}
                            <Table.Row
                                    class="cursor-pointer"
                                    onclick={() => selectedEntry = entry}
                            >
                                <!-- Level -->
                                <Table.Cell>
                                    {@const Icon = levelIcon[entry.level.toLowerCase()]}
                                    <div class="flex items-center gap-2 {levelClass[entry.level.toLowerCase()]}">
                                        {#if Icon}<Icon class="size-5" />{/if}
                                        <span class="capitalize">{entry.level}</span>
                                    </div>
                                </Table.Cell>

                                <!-- Timestamp -->
                                <Table.Cell class="whitespace-nowrap">
                                    {entry.timestamp ?? '—'}
                                </Table.Cell>

                                <!-- Description -->
                                <Table.Cell class="whitespace-nowrap">
                                    {entry.message}
                                </Table.Cell>
                            </Table.Row>
                        {/each}
                    {/if}
                </Table.Body>
            </Table.Root>
        </Card.Content>
    </Card.Root>
</section>

<Dialog.Root open={selectedEntry !== null} onOpenChange={(open) => { if (!open) selectedEntry = null; }}>
    <Dialog.Content class="sm:max-w-7xl">
        <Dialog.Header>
            <Dialog.Title class="flex items-center gap-4">
                {#if selectedEntry}
                    {@const Icon = levelIcon[selectedEntry.level.toLowerCase()]}
                    <span class="flex items-center gap-2 {levelClass[selectedEntry.level.toLowerCase()]} capitalize">
                        {#if Icon}<Icon class="size-5" />{/if}
                        {selectedEntry.level}
                    </span>

                    {#if selectedEntry?.timestamp}
                        <span>{selectedEntry.timestamp}</span>
                    {/if}

                    <span>{selectedEntry.message}</span>
                {/if}
            </Dialog.Title>
        </Dialog.Header>
        <Dialog.Body>
            {#if selectedEntry && selectedEntry.extra}
                <pre class="overflow-x-auto whitespace-pre-wrap break-words">{selectedEntry.extra}</pre>
            {/if}
        </Dialog.Body>
    </Dialog.Content>
</Dialog.Root>
