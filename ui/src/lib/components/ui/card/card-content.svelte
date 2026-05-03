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
	data-slot="card-content"
	class={cn("p-6 gap-6 flex flex-col h-full group-data-[size=sm]/card:px-4 overflow-y-auto relative", className)}
	{...restProps}
>
	{@render children?.()}
</div>
