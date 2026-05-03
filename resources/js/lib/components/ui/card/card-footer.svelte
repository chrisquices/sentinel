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
	data-slot="card-footer"
	class={cn("rounded-b-lg border-t p-6 group-data-[size=sm]/card:p-4 flex items-center", className)}
	{...restProps}
>
	{@render children?.()}
</div>
