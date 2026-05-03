<script lang="ts">
    import {onMount} from 'svelte';
    import * as Card from '$lib/components/ui/card';
    import {Cpu} from 'lucide-svelte';
    import type {SystemData} from '$lib/types';
    import Chart from 'chart.js/auto';

    // region --- General ----------------------------------------------------------------------------------------------
    const path = (window as any).__vulcanSentinel?.basePath ?? 'vulcan-sentinel';

    function getPrimaryColor() {
        return getComputedStyle(document.documentElement).getPropertyValue('--primary').trim();
    }

    // endregion

    // region --- System -----------------------------------------------------------------------------------------------
    let systemData: SystemData | null = $state((window as any).__vulcanSentinel?.system ?? null);

    // CPU History Chart
    let canvas: HTMLCanvasElement;
    let chart: Chart;
    let tooltipEl: HTMLDivElement;

    function initChart() {
        const primary = getPrimaryColor();

        chart = new Chart(canvas, {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                    data: [],
                    borderColor: primary,
                    backgroundColor: primary.replace('oklch(', 'oklch(').replace(')', ' / 0.15)'),
                    borderWidth: 1.5,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 0,
                }],
            },
            options: {
                animation: false,
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {display: false},
                    tooltip: {
                        enabled: false,
                        external: ({chart, tooltip}) => {
                            if (!tooltipEl) return;

                            if (tooltip.opacity === 0) {
                                tooltipEl.style.opacity = '0';
                                return;
                            }

                            const history = systemData?.cpu.history[tooltip.dataPoints?.[0]?.dataIndex];
                            if (!history) return;

                            tooltipEl.innerHTML = `
                            <div class="text-foreground text-sm text-right flex items-center justify-between">
                                <span>Time</span>
                                <span>${history.timeFormatted}</span>
                            </div>

                            <div class="text-foreground text-sm text-right flex items-center justify-between">
                                <span>CPU</span>
                                <span>${history.usageFormatted}</span>
                            </div>
                        `;

                            const rect = chart.canvas.getBoundingClientRect();
                            tooltipEl.style.opacity = '1';
                            tooltipEl.style.left = rect.left + tooltip.caretX + 'px';
                            tooltipEl.style.top = rect.top + tooltip.caretY + 'px';
                        },
                    },
                },
                scales: {
                    x: {display: false},
                    y: {display: false, min: 0, max: 100},
                },
            },
        });
    }

    function updateChart() {
        if (!chart || !systemData?.cpu.history.length) return;
        chart.data.labels = systemData.cpu.history.map(s => s.timeFormatted);
        chart.data.datasets[0].data = systemData.cpu.history.map(s => s.usage);
        chart.update('none');
    }

    onMount(() => {
        initChart();

        const interval = setInterval(async () => {
            const res = await fetch(`/${path}/api/system`);
            systemData = await res.json();
            updateChart();
        }, 1000);

        return () => {
            clearInterval(interval);
            chart?.destroy();
        };
    });

    // endregion
</script>

<section>
    <h2 class="font-semibold text-foreground mb-4">System</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-start">

        <!-- System -->
        <Card.Root>
            <Card.Header>

                <!-- CPU / CPU Cores / CPU Usage -->
                <div class="flex items-center justify-between w-full">
                    <div class="flex items-center gap-2">
                        <Cpu class="w-4 h-4 text-primary"/>
                        <span class="font-medium text-card-foreground">
                            CPU ({systemData?.cpu.cores ?? '—'} {systemData?.cpu.cores === 1 ? 'core' : 'cores'})
                        </span>
                    </div>
                    <span class="font-bold text-card-foreground">{systemData?.cpu.usageFormatted ?? '—'}</span>
                </div>
            </Card.Header>

            <Card.Content class="p-6 h-full">

                <!-- CPU History Chart -->
                <div class="h-24 mb-2 relative">
                    <canvas bind:this={canvas} class="rounded-xl"></canvas>
                    <div
                            bind:this={tooltipEl}
                            class="pointer-events-none w-44 fixed z-50 opacity-0 gap-4 transition-opacity bg-card border border-border rounded-md px-4 py-4 shadow-md -translate-x-1/2 -translate-y-full"
                    ></div>
                </div>

                <div class="space-y-6">

                    <!-- Memory -->
                    <div class="space-y-1">
                        <div class="flex justify-between text-muted-foreground">
                            <span>RAM</span>
                            <span>{systemData?.memory.usedFormatted ?? '—'}
                                / {systemData?.memory.totalFormatted ?? '—'}</span>
                        </div>
                        <div class="w-full bg-muted rounded-full h-1.5">
                            <div class="bg-primary h-1.5 rounded-full"
                                 style="width: {systemData ? (systemData.memory.used / systemData.memory.total * 100) : 0}%"></div>
                        </div>
                    </div>

                    <!-- Storage -->
                    <div class="space-y-1">
                        <div class="flex justify-between text-muted-foreground">
                            <span>Storage</span>
                            <span>{systemData?.storage.usedFormatted ?? '—'}
                                / {systemData?.storage.totalFormatted ?? '—'}</span>
                        </div>
                        <div class="w-full bg-muted rounded-full h-1.5">
                            <div class="bg-muted-foreground h-1.5 rounded-full"
                                 style="width: {systemData ? (systemData.storage.used / systemData.storage.total * 100) : 0}%"></div>
                        </div>
                    </div>
                </div>
            </Card.Content>
        </Card.Root>

    </div>
</section>