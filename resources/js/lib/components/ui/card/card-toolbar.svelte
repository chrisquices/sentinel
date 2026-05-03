<script lang="ts">
    import type { HTMLAttributes } from "svelte/elements";
    import { cn, type WithElementRef } from "$lib/utils.ts";

    let {
        ref = $bindable(null),
        class: className,
        children,
        ...restProps
    }: WithElementRef<HTMLAttributes<HTMLDivElement>> = $props();
</script>

<div
    bind:this={ref}
    data-slot="card-toolbar"
    class={cn("flex items-center justify-between gap-2 px-6", className)}
    {...restProps}
>
    {@render children?.()}
</div>
