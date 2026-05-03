<script lang="ts">
	import { cn, type WithElementRef } from "$lib/utils.ts";
	import type { HTMLAttributes } from "svelte/elements";

	let {
		ref = $bindable(null),
		class: className,
		children,
		...restProps
	}: WithElementRef<HTMLAttributes<HTMLDivElement>> = $props();
</script>

<div
	bind:this={ref}
	data-slot="card-header"
	class={cn(
		"bg-ultra-card gap-6 w-full h-20 border-b border-border px-6 flex items-center group-data-[size=sm]/card:px-5 group/card-header @container/card-header auto-rows-min has-data-[slot=card-action]:grid-cols-[1fr_auto] has-data-[slot=card-description]:grid-rows-[auto_auto]",
		className
	)}
	{...restProps}
>
	{@render children?.()}
</div>
