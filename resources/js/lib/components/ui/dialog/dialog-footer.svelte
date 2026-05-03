<script lang="ts">
    import { cn, type WithElementRef } from "$lib/utils.ts";
    import type { HTMLAttributes } from "svelte/elements";
    import { Dialog as DialogPrimitive } from "bits-ui";
    import { Button } from "$lib/components/ui/button";

    let {
        ref = $bindable(null),
        class: className,
        children,
        showCloseButton = false,
        ...restProps
    }: WithElementRef<HTMLAttributes<HTMLDivElement>> & {
        showCloseButton?: boolean;
    } = $props();
</script>

<div
    bind:this={ref}
    data-slot="dialog-footer"
    class={cn("bg-ultra-card/60 -mx-6 rounded-b-lg p-6 flex flex-col-reverse gap-4 sm:flex-row sm:justify-end", className)}
    {...restProps}
>
    {@render children?.()}
    {#if showCloseButton}
        <DialogPrimitive.Close>
            {#snippet child({ props })}
                <Button variant="outline" {...props}>Close</Button>
            {/snippet}
        </DialogPrimitive.Close>
    {/if}
</div>
