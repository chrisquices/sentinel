<script lang="ts">
    import { cn, type WithElementRef } from '$lib/utils.ts';
    import type { HTMLAttributes } from 'svelte/elements';

    let {
        ref = $bindable(null),
        class: className,
        children,
        selectableItem,
        draggableItem,
        droppableFolder,
        isSelected = false,
        ...restProps
    }: WithElementRef<HTMLAttributes<HTMLTableRowElement>> & {
        selectableItem?: any;
        draggableItem?: any;
        droppableFolder?: any;
        isSelected?: boolean;
    } = $props();
</script>

<tr bind:this={ref}
    data-slot="table-row"
    data-selected={isSelected || undefined}
    class={cn("transition-colors duration-75 w-full cursor-pointer", className)}
    {...restProps}
>
    {@render children?.()}
</tr>
