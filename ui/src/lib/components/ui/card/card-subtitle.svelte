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
	data-slot="card-subtitle"
	class={cn("text-sm flex items-center gap-2 leading-snug text-muted font-medium group-data-[size=sm]/card:text-sm", className)}
	{...restProps}
>
	{@render children?.()}
</div>
