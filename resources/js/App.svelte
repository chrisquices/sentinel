<script lang="ts">
    import {onMount} from 'svelte';
    import Topbar from '$lib/components/ui/Topbar.svelte';
    import System from './pages/System.svelte';
    import Queue from './pages/Queue.svelte';
    import {fetchSystem, fetchQueue} from '$lib/api';

    interface Props {
        projectName?: string;
    }

    let {projectName = 'My Project'}: Props = $props();

    let isDark = $state(false);
    let systemData = $state<any>(null);
    let queueData = $state<any>(null);

    function toggleTheme(): void {
        isDark = !isDark;
        document.documentElement.classList.toggle('dark', isDark);
        localStorage.setItem('vulcan-sentinel-theme', isDark ? 'dark' : 'light');
    }

    onMount(async () => {
        const saved = localStorage.getItem('vulcan-sentinel-theme');
        if (saved === 'dark' || (!saved && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            isDark = true;
            document.documentElement.classList.add('dark');
        }

        const [sysResult, queueResult] = await Promise.allSettled([
            fetchSystem(),
            fetchQueue(),
        ]);

        if (sysResult.status === 'fulfilled') systemData = sysResult.value;
        if (queueResult.status === 'fulfilled') queueData = queueResult.value;
    });
</script>

<div class="min-h-screen transition-colors duration-200">
    <div class="absolute inset-0 -z-10" style="background: linear-gradient(150deg, #050000 0%, #0d0000 20%, #1a000a 40%, #2d0000 60%, #3d0015 80%, #1f000b 100%);"></div>
    <Topbar {isDark} {toggleTheme} {projectName} />
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-10">
        <System initialData={systemData} />
        <Queue initialData={queueData} />
    </main>
</div>