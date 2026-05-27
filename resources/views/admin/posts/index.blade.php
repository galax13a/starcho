<x-layouts::admin title="Posts — Blog">

    <div class="flex items-center justify-between mb-6">
        <div>
            <flux:heading size="xl" level="1" class="mb-0.5">Posts del Blog</flux:heading>
            <flux:text class="text-zinc-500">Gestiona los artículos publicados en el blog del sitio.</flux:text>
        </div>
        <div class="flex flex-wrap items-center justify-end gap-2">
            <button type="button" onclick="Livewire.dispatch('openPostAiCreator')"
                    class="inline-flex items-center gap-2 rounded-xl border border-violet-200 bg-white px-4 py-2 text-sm font-semibold text-violet-700 shadow-sm transition hover:-translate-y-0.5 hover:border-violet-300 hover:bg-violet-50 dark:border-violet-900/50 dark:bg-zinc-900 dark:text-violet-200">
                <i class="fas fa-wand-magic-sparkles text-xs"></i>
                Crear post con AI
            </button>
            <a href="{{ route('admin.posts.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-violet-600 hover:bg-violet-700 text-white text-sm font-medium transition shadow-sm">
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
                Nuevo post
            </a>
        </div>
    </div>

    {{-- Stats --}}
    @php
        $statCards = [
            ['label' => 'Total',       'value' => $stats['total'],     'icon' => 'fas fa-newspaper',    'iconBg' => 'rgba(124,58,237,.12)',  'iconColor' => '#7c3aed', 'tone' => 'stripe'],
            ['label' => 'Publicados',  'value' => $stats['published'], 'icon' => 'fas fa-circle-check', 'iconBg' => 'rgba(16,185,129,.12)',  'iconColor' => '#10b981', 'tone' => 'success'],
            ['label' => 'Borradores',  'value' => $stats['draft'],     'icon' => 'fas fa-pen',          'iconBg' => 'rgba(113,113,122,.12)', 'iconColor' => '#71717a', 'tone' => 'stripe'],
            ['label' => 'Programados', 'value' => $stats['scheduled'], 'icon' => 'fas fa-clock',        'iconBg' => 'rgba(59,130,246,.12)',  'iconColor' => '#3b82f6', 'tone' => 'info'],
            ['label' => 'Privados',    'value' => $stats['private'],   'icon' => 'fas fa-lock',         'iconBg' => 'rgba(236,72,153,.12)',  'iconColor' => '#ec4899', 'tone' => 'danger'],
        ];
    @endphp

    <div class="posts-top-stats grid grid-cols-2 md:grid-cols-5 gap-3 mb-6">
        @foreach($statCards as $card)
            <x-starcho-card-admin-stats
                :label="$card['label']"
                :value="$card['value']"
                :icon="$card['icon']"
                :iconBg="$card['iconBg']"
                :iconColor="$card['iconColor']"
                :tone="$card['tone']"
            />
        @endforeach
    </div>

    <style>
        .posts-top-stats .sa-stat-card { padding: .8rem; min-height: 106px; }
        .posts-top-stats .sa-stat-label { font-size: .68rem; line-height: 1rem; }
        .posts-top-stats .sa-stat-value { font-size: 1.15rem; line-height: 1.35rem; }
        .posts-top-stats .sa-stat-icon  { width: 1.85rem; height: 1.85rem; font-size: .78rem; }
    </style>

    <livewire:admin.posts-table />
    <livewire:admin.post-ai-creator />

</x-layouts::admin>
