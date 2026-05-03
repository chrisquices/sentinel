<script lang="ts">
	import { cn, type WithElementRef } from "$lib/utils.ts";
	import type { HTMLAttributes } from "svelte/elements";

	let {
		ref = $bindable(null),
		class: className,
		children,
		...restProps
	}: WithElementRef<HTMLAttributes<HTMLTableSectionElement>> = $props();
</script>

<tbody
    bind:this={ref}
    data-slot="table-body"
    class={cn(
            // "bg-ultra-card/0 hover:bg-ultra-card/40",
            // "[&>div:nth-child(even)_tr]:bg-ultra-card/30",
            "[&>div:not(:last-child)_tr]:border-b",
            "[&>div:not(:last-child)_tr]:border-border",
            "[&>div_tr:not([data-selected])]:hover:bg-secondary/60",
            "[&>div_tr[data-selected]]:bg-secondary/80!",
            "[&>div_tr[data-selected]]:hover:bg-secondary/60!",
            className
        )}
    {...restProps}
>
{@render children?.()}
</tbody>
