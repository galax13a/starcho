@assets
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.45.1/dist/apexcharts.min.js"></script>
@endassets

<x-layouts::admin :title="__('admin_pages.tasks_index')">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <flux:heading size="xl" level="1" class="mb-0.5">{{ __('admin_ui.tasks.heading') }}</flux:heading>
            <flux:text class="text-zinc-500">{{ __('admin_ui.tasks.description') }}</flux:text>
        </div>
    </div>

    {{-- Stats Cards --}}
    @php
        $taskCards = [
            ['label' => __('admin_ui.tasks.stats.total'), 'value' => $stats['total'], 'tone' => 'default'],
            ['label' => __('admin_ui.tasks.stats.pending'), 'value' => $stats['pending'], 'tone' => 'muted'],
            ['label' => __('admin_ui.tasks.stats.in_progress'), 'value' => $stats['in_progress'], 'tone' => 'blue'],
            ['label' => __('admin_ui.tasks.stats.completed'), 'value' => $stats['completed'], 'tone' => 'emerald'],
            ['label' => __('admin_ui.tasks.stats.cancelled'), 'value' => $stats['cancelled'], 'tone' => 'muted'],
            ['label' => __('admin_ui.tasks.stats.overdue'), 'value' => $stats['overdue'], 'tone' => 'red'],
            ['label' => __('admin_ui.tasks.stats.due_today'), 'value' => $stats['due_today'], 'tone' => 'violet'],
        ];
    @endphp

    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-3 mb-6">
        @foreach($taskCards as $card)
            <x-starcho-card-statsOne
                :label="$card['label']"
                :value="$card['value']"
                :tone="$card['tone']"
            />
        @endforeach
    </div>

    {{-- Charts Row --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">

        {{-- Donut: by status --}}
        <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 p-5 shadow-sm"
             x-data="{
                init() {
                    const isDark = document.documentElement.classList.contains('dark');
                    const textColor = isDark ? '#a1a1aa' : '#71717a';
                    const el = this.$refs.donut;
                    if (el._apexChart) {
                        el._apexChart.destroy();
                    }
                    el.innerHTML = '';
                    el._apexChart = new ApexCharts(el, {
                        series: @js($byStatus->values()),
                        labels: @js($byStatus->keys()),
                        chart: { type: 'donut', height: 240, background: 'transparent', toolbar: { show: false } },
                        colors: ['#64748b','#3b82f6','#10b981','#6b7280'],
                        dataLabels: { enabled: false },
                        legend: { position: 'bottom', labels: { colors: textColor } },
                        plotOptions: { pie: { donut: { size: '65%', labels: { show: true, total: { show: true, label: '{{ __('admin_ui.tasks.stats.total') }}', color: textColor } } } } },
                        stroke: { width: 0 },
                        theme: { mode: isDark ? 'dark' : 'light' }
                    });
                    el._apexChart.render();
                }
             }" x-init="init">
            <h3 class="text-sm font-semibold text-zinc-700 dark:text-zinc-300 mb-3">{{ __('admin_ui.tasks.chart.by_status') }}</h3>
            <div x-ref="donut"></div>
        </div>

        {{-- Bar: last 7 days --}}
        <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 p-5 shadow-sm"
             x-data="{
                init() {
                    const isDark = document.documentElement.classList.contains('dark');
                    const textColor = isDark ? '#a1a1aa' : '#71717a';
                    const gridColor = isDark ? '#3f3f46' : '#f4f4f5';
                    const el = this.$refs.bar;
                    if (el._apexChart) {
                        el._apexChart.destroy();
                    }
                    el.innerHTML = '';
                    el._apexChart = new ApexCharts(el, {
                        series: [{ name: '{{ __('admin_ui.tasks.heading') }}', data: @js($dailyCounts) }],
                        chart: { type: 'bar', height: 240, background: 'transparent', toolbar: { show: false } },
                        xaxis: { categories: @js($dailyLabels), labels: { style: { colors: textColor } } },
                        yaxis: { labels: { style: { colors: [textColor] } }, tickAmount: 4 },
                        colors: ['#00f2ff'],
                        plotOptions: { bar: { borderRadius: 6, columnWidth: '55%' } },
                        dataLabels: { enabled: false },
                        grid: { borderColor: gridColor },
                        fill: { type: 'gradient', gradient: { shade: 'dark', type: 'vertical', shadeIntensity: 0.3, gradientToColors: ['#a855f7'], opacityFrom: 1, opacityTo: 0.8 } },
                        theme: { mode: isDark ? 'dark' : 'light' }
                    });
                    el._apexChart.render();
                }
             }" x-init="init">
            <h3 class="text-sm font-semibold text-zinc-700 dark:text-zinc-300 mb-3">{{ __('admin_ui.tasks.chart.created_last_7_days') }}</h3>
            <div x-ref="bar"></div>
        </div>

        {{-- Area: last 6 months --}}
        <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 p-5 shadow-sm"
             x-data="{
                init() {
                    const isDark = document.documentElement.classList.contains('dark');
                    const textColor = isDark ? '#a1a1aa' : '#71717a';
                    const gridColor = isDark ? '#3f3f46' : '#f4f4f5';
                    const el = this.$refs.area;
                    if (el._apexChart) {
                        el._apexChart.destroy();
                    }
                    el.innerHTML = '';
                    el._apexChart = new ApexCharts(el, {
                        series: [{ name: '{{ __('admin_ui.tasks.heading') }}', data: @js($monthlyCounts) }],
                        chart: { type: 'area', height: 240, background: 'transparent', toolbar: { show: false }, sparkline: { enabled: false } },
                        xaxis: { categories: @js($monthlyLabels), labels: { style: { colors: textColor } } },
                        yaxis: { labels: { style: { colors: [textColor] } }, tickAmount: 4 },
                        colors: ['#ff00c8'],
                        fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.05, stops: [0, 100] } },
                        dataLabels: { enabled: false },
                        stroke: { curve: 'smooth', width: 2 },
                        grid: { borderColor: gridColor },
                        markers: { size: 4, colors: ['#ff00c8'], strokeColors: '#fff', strokeWidth: 2 },
                        theme: { mode: isDark ? 'dark' : 'light' }
                    });
                    el._apexChart.render();
                }
             }" x-init="init">
            <h3 class="text-sm font-semibold text-zinc-700 dark:text-zinc-300 mb-3">{{ __('admin_ui.tasks.chart.created_last_6_months') }}</h3>
            <div x-ref="area"></div>
        </div>

    </div>

    {{-- Table --}}
    <livewire:admin.tasks-table />
    <livewire:admin.task-modal />
    <livewire:admin.tasks-import-modal />

</x-layouts::admin>
