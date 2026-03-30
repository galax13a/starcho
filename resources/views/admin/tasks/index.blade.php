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
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-3 mb-6">
        {{-- Total --}}
        <div class="col-span-1 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 p-4 flex flex-col gap-1 shadow-sm">
            <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('admin_ui.tasks.stats.total') }}</span>
            <span class="text-3xl font-bold text-zinc-800 dark:text-zinc-100">{{ $stats['total'] }}</span>
        </div>
        {{-- Pendientes --}}
        <div class="col-span-1 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 p-4 flex flex-col gap-1 shadow-sm">
            <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('admin_ui.tasks.stats.pending') }}</span>
            <span class="text-3xl font-bold text-zinc-500">{{ $stats['pending'] }}</span>
        </div>
        {{-- En progreso --}}
        <div class="col-span-1 rounded-xl border border-blue-200 dark:border-blue-800/50 bg-blue-50 dark:bg-blue-900/20 p-4 flex flex-col gap-1 shadow-sm">
            <span class="text-xs font-medium text-blue-600 dark:text-blue-400 uppercase tracking-wider">{{ __('admin_ui.tasks.stats.in_progress') }}</span>
            <span class="text-3xl font-bold text-blue-600 dark:text-blue-400">{{ $stats['in_progress'] }}</span>
        </div>
        {{-- Completadas --}}
        <div class="col-span-1 rounded-xl border border-emerald-200 dark:border-emerald-800/50 bg-emerald-50 dark:bg-emerald-900/20 p-4 flex flex-col gap-1 shadow-sm">
            <span class="text-xs font-medium text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">{{ __('admin_ui.tasks.stats.completed') }}</span>
            <span class="text-3xl font-bold text-emerald-600 dark:text-emerald-400">{{ $stats['completed'] }}</span>
        </div>
        {{-- Canceladas --}}
        <div class="col-span-1 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 p-4 flex flex-col gap-1 shadow-sm">
            <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('admin_ui.tasks.stats.cancelled') }}</span>
            <span class="text-3xl font-bold text-zinc-400">{{ $stats['cancelled'] }}</span>
        </div>
        {{-- Vencidas --}}
        <div class="col-span-1 rounded-xl border border-red-200 dark:border-red-800/50 bg-red-50 dark:bg-red-900/20 p-4 flex flex-col gap-1 shadow-sm">
            <span class="text-xs font-medium text-red-600 dark:text-red-400 uppercase tracking-wider">{{ __('admin_ui.tasks.stats.overdue') }}</span>
            <span class="text-3xl font-bold text-red-600 dark:text-red-400">{{ $stats['overdue'] }}</span>
        </div>
        {{-- Hoy --}}
        <div class="col-span-1 rounded-xl border border-violet-200 dark:border-violet-800/50 bg-violet-50 dark:bg-violet-900/20 p-4 flex flex-col gap-1 shadow-sm">
            <span class="text-xs font-medium text-violet-600 dark:text-violet-400 uppercase tracking-wider">{{ __('admin_ui.tasks.stats.due_today') }}</span>
            <span class="text-3xl font-bold text-violet-600 dark:text-violet-400">{{ $stats['due_today'] }}</span>
        </div>
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

</x-layouts::admin>
