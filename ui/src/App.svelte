<script lang="ts">
    import { onMount } from 'svelte';
    import Topbar from '$lib/components/ui/Topbar.svelte';
    import System from './components/System.svelte';
    import Queue from './components/Queue.svelte';
    import { fetchSystem, fetchQueue } from '$lib/api';

    interface Props {
        projectName?: string;
    }

    let { projectName = 'My Project' }: Props = $props();

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
    <div class="fixed inset-0 -z-10" style="background: linear-gradient(0deg, rgba(0,0,0,0.6), rgba(0,0,0,0.6)), radial-gradient(68% 58% at 50% 50%, #c81e3a 0%, #a51d35 16%, #7d1a2f 32%, #591828 46%, #3c1722 60%, #2a151d 72%, #1f1317 84%, #141013 94%, #0a0a0a 100%), radial-gradient(90% 75% at 50% 50%, rgba(228,42,66,0.06) 0%, rgba(228,42,66,0) 55%), radial-gradient(150% 120% at 8% 8%, rgba(0,0,0,0) 42%, #0b0a0a 82%, #070707 100%), radial-gradient(150% 120% at 92% 92%, rgba(0,0,0,0) 42%, #0b0a0a 82%, #070707 100%), radial-gradient(60% 50% at 50% 60%, rgba(240,60,80,0.06), rgba(0,0,0,0) 60%), #050505"></div>
    <div class="fixed inset-0 -z-10 pointer-events-none" style="background-image: radial-gradient(circle at 50% 50%, rgba(0,0,0,0) 55%, rgba(0,0,0,0.5) 100%); opacity: 0.95;"></div>
    <Topbar {isDark} {toggleTheme} {projectName} />
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-10">
        <System initialData={systemData} />
        <Queue initialData={queueData} />
    </main>
</div>