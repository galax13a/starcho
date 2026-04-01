<x-layouts::admin>
    <div class="sa-page">

        <!-- Page header -->
        <div class="sa-page-header">
            <h1 class="sa-page-title">
                <i class="fas fa-ban"></i>
                {{ __('admin_ui.users_ban.heading') }}
            </h1>
            <p class="sa-page-description">{{ __('admin_ui.users_ban.description') }}</p>
        </div>

        <!-- Stats cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
            <x-starcho-card-admin-stats
                :label="__('admin_ui.users_ban.stats.total_banned')"
                :value="$stats['total_banned']"
                icon="fas fa-ban"
                iconBg="rgba(239,68,68,.12)"
                iconColor="#ef4444"
                tone="danger"
            />
            <x-starcho-card-admin-stats
                :label="__('admin_ui.users_ban.stats.permanent')"
                :value="$stats['permanent']"
                icon="fas fa-lock"
                iconBg="rgba(124,58,237,.12)"
                iconColor="#7c3aed"
                tone="stripe"
            />
            <x-starcho-card-admin-stats
                :label="__('admin_ui.users_ban.stats.temporary')"
                :value="$stats['temporary']"
                icon="fas fa-clock"
                iconBg="rgba(249,115,22,.12)"
                iconColor="#f97316"
                tone="warning"
            />
            <x-starcho-card-admin-stats
                :label="__('admin_ui.users_ban.stats.lifted')"
                :value="$stats['lifted']"
                icon="fas fa-unlock"
                iconBg="rgba(16,185,129,.12)"
                iconColor="#10b981"
                tone="success"
            />
        </div>

        <!-- PowerGrid + modal de ban (todo en un solo componente Livewire) -->
        <livewire:admin.user-bans-table />

    </div>
</x-layouts::admin>
