<script lang="ts">
	import type { HTMLAttributes } from "svelte/elements";
	import { cn, type WithElementRef } from "$lib/utils.ts";

    let {
        ref = $bindable(null),
        class: className,
        children,
        size = "default",
        ...restProps
    }: WithElementRef<HTMLAttributes<HTMLDivElement>> & {
        size?: "default" | "sm";
    } = $props();
</script>

<div
	bind:this={ref}
	data-slot="card"
	data-size={size}
	class={cn("bg-card text-card-foreground overflow-hidden rounded-lg border border-border group/card flex flex-col shadow-2xl", className)}
	{...restProps}
>
	{@render children?.()}
</div>
