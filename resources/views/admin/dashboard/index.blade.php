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
