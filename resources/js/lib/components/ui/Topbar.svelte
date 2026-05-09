<script lang="ts">
    import { Sun, Moon, Flame } from 'lucide-svelte';

    interface Props {
        isDark?: boolean;
        toggleTheme?: () => void;
        projectName?: string;
    }

    let { isDark = false, toggleTheme = () => {}, projectName = 'My Project' }: Props = $props();
</script>

<header class="sticky top-0 z-40 border-b border-border bg-sidebar backdrop-blur-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            <div class="flex items-center gap-4">
                <div class="flex items-center justify-center w-8 h-8 rounded-full bg-primary">
                    <Flame class="size-4.5 text-primary-foreground" />
                </div>
                <div>
                    <span class="font-semibold text-foreground">Sentinel</span>
                    <span class="ml-2 text-muted-foreground">{projectName}</span>
                </div>
            </div>

            <button
                onclick={toggleTheme}
                class="p-2 rounded-lg text-muted-foreground hover:bg-muted transition-colors"
                aria-label="Toggle theme"
            >
                {#if isDark}
                    <Sun class="w-5 h-5" />
                {:else}
                    <Moon class="w-5 h-5" />
                {/if}
            </button>
        </div>
    </div>
</header>
