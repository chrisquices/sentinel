<script lang="ts">
    import * as Card from '$lib/components/ui/card';
    import {Badge} from '$lib/components/ui/badge';
    import type {RuntimeData} from '$lib/types';
    import {Cpu} from 'lucide-svelte';
    import {Separator} from "$lib/components/ui/separator";

    interface Props {
        initialData?: RuntimeData | null;
        class?: string;
    }

    let {initialData = null, class: className = ''}: Props = $props();

    function hitRatioClass(ratio: number | null): string {
        if (ratio === null) return 'text-muted-foreground';
        if (ratio >= 90) return 'text-green-500';
        if (ratio >= 70) return 'text-amber-500';
        return 'text-red-500';
    }
</script>

<section class="{className} h-full flex flex-col">

    <h2 class="font-semibold text-foreground mb-4 flex items-center gap-2"><Cpu class="size-4"/>Runtime</h2>

    <Card.Root class="h-full">
        {#if initialData}
            <Card.Header>
                <div class="flex items-center justify-between w-full">
                    <div class="flex items-center gap-2">
                        <span class="font-medium text-card-foreground">PHP {initialData?.phpVersion ?? '—'}</span>
                    </div>
                    <span class="text-muted-foreground  font-mono">{initialData?.sapi ?? ''}</span>
                </div>
            </Card.Header>

            <Card.Content class="p-6 h-full">
                <div class="flex gap-8">

                    <div class="space-y-4 flex-1">
                        <div class="flex justify-between items-center">
                            <span class="text-muted-foreground">Memory Limit</span>
                            <span class="font-mono  text-card-foreground">{initialData?.memoryLimit ?? '—'}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-muted-foreground">Max Execution</span>
                            <span class="font-mono  text-card-foreground">{initialData?.maxExecutionTime ?? '—'}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-muted-foreground">Upload Max</span>
                            <span class="font-mono  text-card-foreground">{initialData?.uploadMaxFilesize ?? '—'}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-muted-foreground">Post Max</span>
                            <span class="font-mono  text-card-foreground">{initialData?.postMaxSize ?? '—'}</span>
                        </div>
                    </div>

                    <Separator orientation="vertical" class="h-auto" />

                    {#if initialData?.opcache.enabled}
                        <div class="space-y-4 flex-1">
                            <div class="flex justify-between items-center">
                                <span class="text-muted-foreground">OPcache</span>
                                <Badge variant="outline" class="border-green-500/30 bg-green-500/10 text-green-600 dark:text-green-400">
                                    enabled
                                </Badge>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-muted-foreground">Hit Ratio</span>
                                <span class="font-mono  font-bold {hitRatioClass(initialData.opcache.hitRatio)}">
                            {initialData.opcache.hitRatio !== null ? initialData.opcache.hitRatio + '%' : '—'}
                        </span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-muted-foreground">Memory Used</span>
                                <span class="font-mono  text-card-foreground">{initialData.opcache.memoryUsed}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-muted-foreground">Memory Free</span>
                                <span class="font-mono  text-card-foreground">{initialData.opcache.memoryFree}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-muted-foreground">Cached Scripts</span>
                                <span class="font-mono  text-card-foreground">{initialData.opcache.cachedScripts}</span>
                            </div>
                        </div>
                    {/if}

                </div>
            </Card.Content>
        {/if}
    </Card.Root>
</section>
