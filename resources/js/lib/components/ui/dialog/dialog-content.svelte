<script lang="ts">
    import { Dialog as DialogPrimitive } from "bits-ui";
    import DialogPortal from "./dialog-portal.svelte";
    import type { Snippet } from "svelte";
    import * as Dialog from "./index.js";
    import { cn, type WithoutChildrenOrChild } from "$lib/utils.ts";
    import type { ComponentProps } from "svelte";
    import { Button } from "$lib/components/ui/button/index.js";
    import { X as XIcon } from 'lucide-svelte';

    let {
        ref = $bindable(null),
        class: className,
        portalProps,
        children,
        showCloseButton = true,
        ...restProps
    }: WithoutChildrenOrChild<DialogPrimitive.ContentProps> & {
        portalProps?: WithoutChildrenOrChild<ComponentProps<typeof DialogPortal>>;
        children: Snippet;
        showCloseButton?: boolean;
    } = $props();
</script>

<DialogPortal {...portalProps}>
    <Dialog.Overlay />
    <DialogPrimitive.Content
        bind:ref
        data-slot="dialog-content"
        class={cn(
          "bg-popover border border-border max-h-[calc(100vh-10rem)] data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0 data-[state=closed]:zoom-out-95 data-[state=open]:zoom-in-95 fixed start-[50%] top-[50%] z-50 flex flex-col w-full max-w-[calc(100%-2rem)] translate-x-[-50%] translate-y-[-50%] rounded-lg duration-300 sm:max-w-lg px-6",
          className
       )}
        {...restProps}
    >
        {@render children?.()}

        {#if showCloseButton}
            <DialogPrimitive.Close data-slot="dialog-close">
                {#snippet child({ props })}
                    <Button variant="ghost" class="absolute top-4 right-4" size="icon-sm" {...props}>
                        <XIcon />
                        <span class="sr-only">Close</span>
                    </Button>
                {/snippet}
            </DialogPrimitive.Close>
        {/if}
    </DialogPrimitive.Content>
</DialogPortal>
