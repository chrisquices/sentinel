<script>
    import { onMount } from 'svelte';
    import Header from './components/layout/Header.svelte';
    import SystemSection from './components/system/SystemSection.svelte';
    import JobsSection from './components/jobs/JobsSection.svelte';
    import SchedulerSection from './components/scheduler/SchedulerSection.svelte';
    import LogsSection from './components/logs/LogsSection.svelte';

    export let projectName = 'My Project';

    let isDark = false;

    function toggleTheme() {
        isDark = !isDark;
        document.documentElement.classList.toggle('dark', isDark);
        localStorage.setItem('sentinel-theme', isDark ? 'dark' : 'light');
    }

    onMount(() => {
        const saved = localStorage.getItem('sentinel-theme');
        if (saved === 'dark' || (!saved && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            isDark = true;
            document.documentElement.classList.add('dark');
        }
    });
</script>

<div class="min-h-screen bg-gray-50 dark:bg-gray-950 transition-colors duration-200">
    <Header {isDark} {toggleTheme} {projectName} />
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-10">
        <SystemSection />
        <JobsSection />
        <SchedulerSection />
        <LogsSection />
    </main>
</div>
