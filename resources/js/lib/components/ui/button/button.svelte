<script lang="ts" module>
    import { cn, type WithElementRef } from "$lib/utils.ts";
    import type { HTMLAnchorAttributes, HTMLButtonAttributes } from "svelte/elements";
    import { type VariantProps, tv } from "tailwind-variants";

    export const buttonVariants = tv({
        base: "cursor-pointer text-sm focus-visible:border-ring focus-visible:ring-ring/50 aria-invalid:ring-destructive/20 aria-invalid:border-destructive rounded-md border border-transparent bg-clip-padding font-medium focus-visible:ring-3 active:not-aria-[haspopup]:translate-y-px aria-invalid:ring-3 [&_svg:not([class*='size-'])]:size-4 group/button inline-flex shrink-0 items-center justify-center whitespace-nowrap transition-all outline-none select-none disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:shrink-0",
        variants: {
            variant: {
                default: "bg-primary text-primary-foreground [a]:hover:bg-primary/80",
                outline: "border-border bg-transparent hover:bg-primary/30 hover:text-foreground aria-expanded:bg-muted aria-expanded:text-foreground",
                secondary: "bg-secondary text-secondary-foreground hover:bg-secondary-foreground/10 border border-border aria-expanded:bg-secondary aria-expanded:text-secondary-foreground",
                ghost: "hover:bg-muted hover:text-foreground aria-expanded:bg-muted aria-expanded:text-foreground",
                destructive: "bg-destructive hover:bg-destructive/80 focus-visible:ring-destructive/80 text-foreground",
                success: "bg-success hover:bg-success/80 focus-visible:ring-success/80 text-foreground",
                link: "text-primary underline-offset-4 hover:underline",
            },
            size: {
                default: "h-8 gap-1.5 px-2.5 has-data-[icon=inline-end]:pr-2 has-data-[icon=inline-start]:pl-2",
                xs: "h-6 gap-1 rounded-[min(var(--radius-md),10px)] px-2 text-sm in-data-[slot=button-group]:rounded-md has-data-[icon=inline-end]:pr-1.5 has-data-[icon=inline-start]:pl-1.5 [&_svg:not([class*='size-'])]:size-3",
                sm: "h-7 gap-2 rounded-[min(var(--radius-md),12px)] px-2.5 text-[0.8rem] in-data-[slot=button-group]:rounded-md has-data-[icon=inline-end]:pr-1.5 has-data-[icon=inline-start]:pl-1.5 [&_svg:not([class*='size-'])]:size-3.5",
                lg: "h-9 gap-2 px-2.5 has-data-[icon=inline-end]:pr-2 has-data-[icon=inline-start]:pl-2",
                icon: "size-8",
                "icon-xs": "size-6 rounded-[min(var(--radius-md),10px)] in-data-[slot=button-group]:rounded-md [&_svg:not([class*='size-'])]:size-3",
                "icon-sm": "size-7 rounded-[min(var(--radius-md),12px)] in-data-[slot=button-group]:rounded-md",
                "icon-lg": "size-9",
            },
        },
        defaultVariants: {
            variant: "default",
            size: "default",
        },
    });

    export type ButtonVariant = VariantProps<typeof buttonVariants>["variant"];
    export type ButtonSize = VariantProps<typeof buttonVariants>["size"];

    type SvelteAction<T = unknown> = (node: HTMLElement, params?: T) => { destroy?: () => void; update?: (params?: T) => void } | void;

    export type ButtonProps = WithElementRef<HTMLButtonAttributes> &
        WithElementRef<HTMLAnchorAttributes> & {
        variant?: ButtonVariant;
        size?: ButtonSize;
        action?: SvelteAction<any>;
        actionParams?: any;
    };
</script>

<script lang="ts">
    let {
        class: className,
        variant = "default",
        size = "default",
        ref = $bindable(null),
        href = undefined,
        type = "button",
        disabled,
        action,
        actionParams,
        children,
        ...restProps
    }: ButtonProps = $props();

    const resolvedAction = $derived(action ?? (() => ({})));
</script>

{#if href}
    <a
        bind:this={ref}
        data-slot="button"
        class={cn(buttonVariants({ variant, size }), className)}
        {href}
        aria-disabled={disabled}
        tabindex={disabled ? -1 : undefined}
        use:resolvedAction={actionParams}
        {...restProps}
    >
        {@render children?.()}
    </a>
{:else}
    <button
        bind:this={ref}
        data-slot="button"
        class={cn(buttonVariants({ variant, size }), className)}
        {type}
        {disabled}
        use:resolvedAction={actionParams}
        {...restProps}
    >
        {@render children?.()}
    </button>
{/if}
