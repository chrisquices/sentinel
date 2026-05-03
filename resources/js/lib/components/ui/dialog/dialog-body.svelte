<script lang="ts">
    import { cn, type WithElementRef } from '$lib/utils.ts';
    import type { HTMLAttributes } from 'svelte/elements';

    let {
        ref = $bindable(null),
        class: className,
        children,
        ...restProps
    }: WithElementRef<HTMLAttributes<HTMLDivElement>> = $props();
</script>

<div class={cn("flex flex-col h-full min-h-0 gap-6 overflow-y-auto p-6 -mx-6 border-y border-border", className)} {...restProps}>
    {@render children?.()}
</div>
