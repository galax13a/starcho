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

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <a href="{{ route('admin.tasks.index') }}" wire:navigate class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 p-5 shadow-sm hover:border-cyan-300 dark:hover:border-cyan-600 transition">
            <div class="text-sm font-semibold text-zinc-700 dark:text-zinc-300">{{ __('admin_ui.dashboard.quick.tasks') }}</div>
            <div class="text-xs text-zinc-500 mt-1">{{ __('admin_ui.dashboard.quick.tasks_help') }}</div>
        </a>
        <a href="{{ route('admin.modules.index') }}" wire:navigate class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 p-5 shadow-sm hover:border-cyan-300 dark:hover:border-cyan-600 transition">
            <div class="text-sm font-semibold text-zinc-700 dark:text-zinc-300">{{ __('admin_ui.dashboard.quick.modules') }}</div>
            <div class="text-xs text-zinc-500 mt-1">{{ __('admin_ui.dashboard.quick.modules_help') }}</div>
        </a>
    </div>
</x-layouts::admin>
