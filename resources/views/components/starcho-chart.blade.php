{{--
    x-starcho-chart — Componente universal de gráficas ApexCharts para Starcho.

    Props obligatorias:
      :series      → array  — Datos de la serie.
                              Donut/Pie/RadialBar: [12, 8, 3, 1]
                              Bar/Area/Line/Heatmap: [['name'=>'Tasks','data'=>[1,4,2]]]
                              Scatter: [['name'=>'Grupo','data'=>[[x,y],[x,y]]]]

    Props opcionales:
      type         → string — donut | pie | bar | area | line | radialBar | heatmap | scatter
                              Default: 'bar'
      :title       → string — Título visible encima de la gráfica.
      :labels      → array  — Etiquetas de segmentos (donut, pie, radialBar).
      :categories  → array  — Etiquetas del eje X  (bar, area, line, heatmap, scatter).
      :colors      → array  — Paleta hex personalizada. Si vacía usa la paleta Starcho.
      height       → int    — Alto en px. Default: 240.
      :totalLabel  → string — Texto del total en el centro del donut. Default: 'Total'.
      :gradient    → bool   — Activa relleno degradado en bar. Default: true.

    Uso mínimo:
      <x-starcho-chart
          type="donut"
          :title="__('admin_ui.tasks.chart.by_status')"
          :series="$byStatus->values()->toArray()"
          :labels="$byStatus->keys()->toArray()"
      />

    Portabilidad:
      El componente depende únicamente de ApexCharts (CDN o npm) y Alpine.js 3.
      Para usarlo en otro proyecto basta con copiar este archivo Blade y garantizar
      que la página carga ApexCharts antes de Alpine, por ejemplo agregando en la
      vista padre:
          @assets
              <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.45.1/dist/apexcharts.min.js"></script>
          @endassets
--}}

@props([
    'type'       => 'bar',
    'title'      => '',
    'series'     => [],
    'labels'     => [],
    'categories' => [],
    'colors'     => [],
    'height'     => 240,
    'totalLabel' => 'Total',
    'gradient'   => true,
])

@php
    $scType       = $type;
    $scHeight     = (int) $height;
    $scSeries     = $series;
    $scLabels     = $labels;
    $scCategories = $categories;
    $scColors     = $colors;
    $scTotal      = $totalLabel;
    $scGradient   = (bool) $gradient;
@endphp

<div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 p-5 shadow-sm"
     x-data="{
         init() {
             const isDark  = document.documentElement.classList.contains('dark');
             const text    = isDark ? '#a1a1aa' : '#71717a';
             const grid    = isDark ? '#3f3f46' : '#f4f4f5';
             const el      = this.$el.querySelector('[data-sc-chart]');
             if (!el) return;
             if (el._apexChart) { el._apexChart.destroy(); }
             el.innerHTML  = '';

             const type       = @js($scType);
             const series     = @js($scSeries);
             const labels     = @js($scLabels);
             const cats       = @js($scCategories);
             const gradient   = @js($scGradient);
             const palette    = @js($scColors).length
                                ? @js($scColors)
                                : ['#00f2ff','#a855f7','#10b981','#f59e0b','#fe2c55','#64748b'];

             const base = {
                 chart: {
                     type,
                     height: {{ $scHeight }},
                     background: 'transparent',
                     toolbar: { show: false },
                     animations: { enabled: true, speed: 400 },
                 },
                 colors: palette,
                 theme: { mode: isDark ? 'dark' : 'light' },
                 dataLabels: { enabled: false },
             };

             let opts = {};

             if (type === 'donut' || type === 'pie') {
                 opts = {
                     ...base, series, labels,
                     legend: { position: 'bottom', labels: { colors: text } },
                     stroke: { width: 0 },
                     ...(type === 'donut' ? {
                         plotOptions: {
                             pie: {
                                 donut: {
                                     size: '65%',
                                     labels: {
                                         show: true,
                                         total: { show: true, label: @js($scTotal), color: text },
                                     },
                                 },
                             },
                         },
                     } : {}),
                 };

             } else if (type === 'bar') {
                 opts = {
                     ...base, series,
                     xaxis: { categories: cats, labels: { style: { colors: text } } },
                     yaxis: { labels: { style: { colors: [text] } }, tickAmount: 4 },
                     plotOptions: { bar: { borderRadius: 6, columnWidth: '55%' } },
                     grid: { borderColor: grid },
                     fill: gradient
                         ? { type: 'gradient', gradient: { shade: 'dark', type: 'vertical', shadeIntensity: 0.3, gradientToColors: ['#a855f7'], opacityFrom: 1, opacityTo: 0.8 } }
                         : {},
                 };

             } else if (type === 'area' || type === 'line') {
                 opts = {
                     ...base, series,
                     xaxis: { categories: cats, labels: { style: { colors: text } } },
                     yaxis: { labels: { style: { colors: [text] } }, tickAmount: 4 },
                     grid: { borderColor: grid },
                     stroke: { curve: 'smooth', width: 2 },
                     ...(type === 'area' ? {
                         fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.05, stops: [0, 100] } },
                         markers: { size: 4, strokeColors: '#fff', strokeWidth: 2 },
                     } : {}),
                 };

             } else if (type === 'radialBar') {
                 opts = {
                     ...base, series, labels,
                     plotOptions: {
                         radialBar: {
                             hollow: { size: '50%' },
                             dataLabels: {
                                 name:  { color: text },
                                 value: { color: text },
                             },
                         },
                     },
                 };

             } else if (type === 'heatmap') {
                 opts = {
                     ...base, series,
                     xaxis: { categories: cats, labels: { style: { colors: text } } },
                     yaxis: { labels: { style: { colors: [text] } } },
                     grid: { borderColor: grid },
                     plotOptions: { heatmap: { colorScale: { inverse: false } } },
                 };

             } else {
                 /* scatter y tipos no listados */
                 opts = {
                     ...base, series,
                     xaxis: { type: 'numeric', labels: { style: { colors: text } } },
                     yaxis: { labels: { style: { colors: [text] } } },
                     grid: { borderColor: grid },
                     markers: { size: 5 },
                 };
             }

             el._apexChart = new ApexCharts(el, opts);
             el._apexChart.render();
         }
     }"
     x-init="init">

    @if($title)
        <h3 class="text-sm font-semibold text-zinc-700 dark:text-zinc-300 mb-3">{{ $title }}</h3>
    @endif

    <div data-sc-chart></div>

</div>
