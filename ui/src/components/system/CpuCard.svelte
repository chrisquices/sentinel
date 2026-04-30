<script>
    import { onMount } from 'svelte';
    import { Cpu } from 'lucide-svelte';

    let canvas;

    const mock = {
        cpu: 42,
        ram: { used: 6.2, total: 16 },
        storage: { used: 128, total: 512 },
        history: [28, 35, 42, 38, 55, 48, 62, 45, 42, 38, 50, 42],
    };

    onMount(async () => {
        const { Chart, LineElement, PointElement, LineController, CategoryScale, LinearScale, Filler } = await import('chart.js');
        Chart.register(LineElement, PointElement, LineController, CategoryScale, LinearScale, Filler);

        const chart = new Chart(canvas, {
            type: 'line',
            data: {
                labels: mock.history.map((_, i) => `${(mock.history.length - i) * 5}s`),
                datasets: [{
                    data: mock.history,
                    borderColor: 'rgb(249, 115, 22)',
                    backgroundColor: 'rgba(249, 115, 22, 0.08)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 0,
                    borderWidth: 2,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { display: false },
                    y: { display: false, min: 0, max: 100 },
                },
            },
        });

        return () => chart.destroy();
    });
</script>

<div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6">
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-2">
            <Cpu class="w-4 h-4 text-orange-500" />
            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">System</h3>
        </div>
        <span class="text-2xl font-bold text-gray-900 dark:text-gray-100">{mock.cpu}%</span>
    </div>

    <div class="h-24 mb-4">
        <canvas bind:this={canvas}></canvas>
    </div>

    <div class="space-y-3">
        <div>
            <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400 mb-1">
                <span>RAM</span>
                <span>{mock.ram.used} / {mock.ram.total} GB</span>
            </div>
            <div class="w-full bg-gray-100 dark:bg-gray-800 rounded-full h-1.5">
                <div class="bg-orange-500 h-1.5 rounded-full" style="width: {(mock.ram.used / mock.ram.total) * 100}%"></div>
            </div>
        </div>
        <div>
            <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400 mb-1">
                <span>Storage</span>
                <span>{mock.storage.used} / {mock.storage.total} GB</span>
            </div>
            <div class="w-full bg-gray-100 dark:bg-gray-800 rounded-full h-1.5">
                <div class="bg-blue-500 h-1.5 rounded-full" style="width: {(mock.storage.used / mock.storage.total) * 100}%"></div>
            </div>
        </div>
    </div>
</div>
