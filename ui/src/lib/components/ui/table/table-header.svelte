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

<thead
    bind:this={ref}
    data-slot="table-header"
    class={cn("[&_tr]:border-b [&_tr:first-child_th:first-child]:rounded-tl-lg [&_tr:first-child_th:last-child]:rounded-tr-lg", className)}
    {...restProps}
>
{@render children?.()}
</thead>
