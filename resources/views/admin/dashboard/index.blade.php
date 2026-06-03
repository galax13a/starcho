@assets
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.45.1/dist/apexcharts.min.js"></script>
@endassets

<x-layouts::admin :title="__('admin_pages.dashboard_index')">
    <div class="flex items-center justify-between mb-6">
        <div>
            <flux:heading size="xl" level="1" class="mb-0.5">{{ __('admin_ui.dashboard.heading') }}</flux:heading>
            <flux:text class="text-zinc-500">{{ __('admin_ui.dashboard.description') }}</flux:text>
        </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
        <x-starcho-card-admin-stats
            :label="__('admin_ui.dashboard.cards.users')"
            :value="$stats['users']"
            icon="fas fa-users"
            iconBg="rgba(124, 58, 237, .12)"
            iconColor="#7c3aed"
        />

        <x-starcho-card-admin-stats
            :label="__('admin_ui.dashboard.cards.tasks_total')"
            :value="$stats['tasks_total']"
            icon="fas fa-tasks"
            iconBg="rgba(37, 244, 238, .12)"
            iconColor="#25f4ee"
        />

        <x-starcho-card-admin-stats
            :label="__('admin_ui.dashboard.cards.tasks_pending')"
            :value="$stats['tasks_pending']"
            icon="fas fa-hourglass"
            iconBg="rgba(245, 158, 11, .12)"
            iconColor="#f59e0b"
        />

        <x-starcho-card-admin-stats
            :label="__('admin_ui.dashboard.cards.contacts_active')"
            :value="$stats['contacts_active']"
            icon="fas fa-address-book"
            iconBg="rgba(16, 185, 129, .12)"
            iconColor="#10b981"
        />

        <x-starcho-card-admin-stats
            :label="__('admin_ui.dashboard.cards.notes_total')"
            :value="$stats['notes_total']"
            icon="fas fa-sticky-note"
            iconBg="rgba(254, 44, 85, .12)"
            iconColor="#fe2c55"
        />

        <x-starcho-card-admin-stats
            :label="__('admin_ui.dashboard.cards.modules_active')"
            :value="$stats['modules_active']"
            icon="fas fa-cube"
            iconBg="rgba(99, 91, 255, .12)"
            iconColor="#635bff"
        />
    </div>

    <div class="mb-6 rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="grid size-9 place-items-center rounded-lg bg-violet-100 text-violet-600 dark:bg-violet-900/30 dark:text-violet-300">
                        <i class="fas fa-photo-film text-sm"></i>
                    </span>
                    <div>
                        <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Multimedia</h2>
                        <p class="text-xs text-zinc-500">Resumen del storage usado por archivos y copias responsive.</p>
                    </div>
                </div>
            </div>

            <a href="{{ route('admin.media.index') }}" class="inline-flex h-9 items-center gap-2 rounded-lg border border-zinc-200 px-3 text-sm font-semibold text-zinc-600 transition hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-300 dark:hover:bg-zinc-800">
                <i class="fas fa-arrow-up-right-from-square text-xs"></i>
                Abrir biblioteca
            </a>
        </div>

        <div class="mt-4 grid grid-cols-2 gap-3 md:grid-cols-5">
            <div class="rounded-lg bg-zinc-50 p-3 dark:bg-zinc-800/70">
                <p class="text-[11px] uppercase tracking-wide text-zinc-400">Archivos</p>
                <p class="mt-1 text-xl font-bold text-zinc-900 dark:text-zinc-100">{{ $mediaSummary['total'] }}</p>
            </div>
            <div class="rounded-lg bg-zinc-50 p-3 dark:bg-zinc-800/70">
                <p class="text-[11px] uppercase tracking-wide text-zinc-400">Peso total</p>
                <p class="mt-1 text-xl font-bold text-zinc-900 dark:text-zinc-100">{{ $mediaSummary['total_label'] }}</p>
            </div>
            <div class="rounded-lg bg-zinc-50 p-3 dark:bg-zinc-800/70">
                <p class="text-[11px] uppercase tracking-wide text-zinc-400">Originales</p>
                <p class="mt-1 text-lg font-bold text-zinc-900 dark:text-zinc-100">{{ $mediaSummary['original_label'] }}</p>
            </div>
            <div class="rounded-lg bg-zinc-50 p-3 dark:bg-zinc-800/70">
                <p class="text-[11px] uppercase tracking-wide text-zinc-400">Copias</p>
                <p class="mt-1 text-lg font-bold text-zinc-900 dark:text-zinc-100">{{ $mediaSummary['variant_label'] }}</p>
            </div>
            <div class="rounded-lg bg-zinc-50 p-3 dark:bg-zinc-800/70">
                <p class="text-[11px] uppercase tracking-wide text-zinc-400">Multi-size</p>
                <p class="mt-1 text-sm font-bold {{ $mediaSummary['variants_enabled'] ? 'text-emerald-600 dark:text-emerald-400' : 'text-zinc-500' }}">
                    {{ $mediaSummary['variants_enabled'] ? 'Activo' : 'Inactivo' }}
                </p>
                <p class="mt-1 truncate text-[11px] text-zinc-400">
                    {{ $mediaSummary['variants_enabled'] ? collect($mediaSummary['variant_sizes'])->map(fn ($size) => $size . 'px')->implode(', ') : 'Sin copias automáticas' }}
                </p>
            </div>
        </div>

        <div class="mt-3 flex flex-wrap gap-2 text-xs text-zinc-500">
            <span class="rounded-full bg-violet-50 px-2.5 py-1 text-violet-700 dark:bg-violet-900/20 dark:text-violet-200">{{ $mediaSummary['images'] }} imágenes</span>
            <span class="rounded-full bg-blue-50 px-2.5 py-1 text-blue-700 dark:bg-blue-900/20 dark:text-blue-200">{{ $mediaSummary['videos'] }} videos</span>
            <span class="rounded-full bg-amber-50 px-2.5 py-1 text-amber-700 dark:bg-amber-900/20 dark:text-amber-200">{{ $mediaSummary['documents'] }} documentos</span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
        <x-starcho-chart
            type="donut"
            :title="__('admin_ui.dashboard.charts.tasks_by_status')"
            :series="$tasksByStatus->values()->toArray()"
            :labels="$tasksByStatus->keys()->toArray()"
            :colors="['#64748b','#3b82f6','#10b981','#ef4444']"
            :total-label="__('admin_ui.tasks.stats.total')"
        />

        <x-starcho-chart
            type="line"
            :title="__('admin_ui.dashboard.charts.monthly_activity')"
            :series="$monthlySeries"
            :categories="$monthlyLabels"
            :colors="['#00f2ff','#a855f7','#10b981']"
            :height="240"
        />

        <x-starcho-chart
            type="radialBar"
            :title="__('admin_ui.dashboard.charts.modules_health')"
            :series="$modulesSeries"
            :labels="[__('admin_ui.dashboard.charts.modules_on'), __('admin_ui.dashboard.charts.modules_off')]"
            :colors="['#10b981','#ef4444']"
            :height="240"
        />
    </div>

    @php
        $quickLinks = [
            ['route' => 'admin.posts.index',    'icon' => 'fas fa-newspaper',    'color' => '#7c3aed', 'label' => __('Posts'),            'help' => $stats['posts_published'] . ' ' . __('published')],
            ['route' => 'admin.pages.index',    'icon' => 'fas fa-file-alt',     'color' => '#06b6d4', 'label' => __('Pages'),            'help' => $stats['pages_published'] . ' ' . __('published')],
            ['route' => 'admin.tasks.index',    'icon' => 'fas fa-clipboard-list','color' => '#10b981', 'label' => __('admin_ui.dashboard.quick.tasks'),   'help' => __('admin_ui.dashboard.quick.tasks_help')],
            ['route' => 'admin.media.index',    'icon' => 'fas fa-photo-film',    'color' => '#8b5cf6', 'label' => 'Media',            'help' => $mediaSummary['total_label'] . ' total'],
            ['route' => 'admin.users.index',    'icon' => 'fas fa-users',        'color' => '#f59e0b', 'label' => __('Users'),            'help' => $stats['users'] . ' ' . __('total')],
            ['route' => 'admin.site.index',     'icon' => 'fas fa-globe',        'color' => '#ef4444', 'label' => __('Website'),          'help' => __('SEO, metadata, branding')],
            ['route' => 'admin.modules.index',  'icon' => 'fas fa-puzzle-piece', 'color' => '#635bff', 'label' => __('admin_ui.dashboard.quick.modules'), 'help' => $stats['modules_active'] . ' ' . __('active')],
        ];
    @endphp
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
        @foreach ($quickLinks as $link)
        <a href="{{ route($link['route']) }}" wire:navigate
           class="group flex flex-col gap-3 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800/60 p-4 shadow-sm hover:shadow-md transition-all hover:-translate-y-0.5"
           style="border-top: 3px solid {{ $link['color'] }}20; hover:border-color:{{ $link['color'] }}">
            <div class="w-9 h-9 rounded-lg flex items-center justify-center text-sm"
                 style="background:{{ $link['color'] }}18; color:{{ $link['color'] }}">
                <i class="{{ $link['icon'] }}"></i>
            </div>
            <div>
                <div class="text-sm font-semibold text-zinc-800 dark:text-zinc-200 leading-tight">{{ $link['label'] }}</div>
                <div class="text-xs text-zinc-400 mt-0.5 truncate">{{ $link['help'] }}</div>
            </div>
        </a>
        @endforeach
    </div>
</x-layouts::admin>
