<script>
    import { Sun, Moon, Activity } from 'lucide-svelte';

    export let isDark = false;
    export let toggleTheme = () => {};
    export let projectName = 'My Project';
</script>

<header class="sticky top-0 z-40 border-b border-gray-200 dark:border-gray-800 bg-white/80 dark:bg-gray-950/80 backdrop-blur-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-orange-500">
                    <Activity class="w-4 h-4 text-white" />
                </div>
                <div>
                    <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">{projectName}</span>
                    <span class="ml-2 text-xs text-gray-400 dark:text-gray-500">Vulcan Sentinel</span>
                </div>
            </div>

            <button
                on:click={toggleTheme}
                class="p-2 rounded-md text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
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
