<x-layouts::app :title="__('tasks.page_title')">
    <div class="sa-page starchi-kick">
        <div class="sa-page-header starchi-kick-header">
            <div class="sa-page-header-left">
                <h1>{{ __('tasks.page_title') }}</h1>
                <p>{{ __('tasks.page_subtitle') }}</p>
            </div>
            <div class="sa-page-header-right">
                <button onclick="Livewire.dispatch('openTask', {id:0})" class="sc-btn sc-btn-kick starchi-kick-btn">
                    <i class="fas fa-plus"></i> {{ __('tasks.new_task') }}
                </button>
            </div>
        </div>

    {{-- ── Kick stat cards ───────────────────────────────────────── --}}
    @php
        $uid = auth()->id();
        $stats = [
            [
                'label' => __('tasks.stat_total'),
                'value' => \App\Models\Task::where('user_id', $uid)->count(),
                'icon'  => 'fas fa-layer-group',
                'icon_bg' => 'rgba(83,252,24,.10)',
                'icon_color' => '#53fc18',
                'color' => 'sc-kick-total',
            ],
            [
                'label' => __('tasks.stat_pending'),
                'value' => \App\Models\Task::where('user_id', $uid)->where('status', 'pending')->count(),
                'icon'  => 'fas fa-clock',
                'icon_bg' => 'rgba(141,171,138,.16)',
                'icon_color' => '#8dab8a',
                'color' => 'sc-kick-pending',
            ],
            [
                'label' => __('tasks.stat_in_progress'),
                'value' => \App\Models\Task::where('user_id', $uid)->where('status', 'in_progress')->count(),
                'icon'  => 'fas fa-spinner',
                'icon_bg' => 'rgba(37,244,238,.12)',
                'icon_color' => '#25f4ee',
                'color' => 'sc-kick-progress',
            ],
            [
                'label' => __('tasks.stat_completed'),
                'value' => \App\Models\Task::where('user_id', $uid)->where('status', 'completed')->count(),
                'icon'  => 'fas fa-check-circle',
                'icon_bg' => 'rgba(83,252,24,.14)',
                'icon_color' => '#53fc18',
                'color' => 'sc-kick-done',
            ],
            [
                'label' => __('tasks.stat_overdue'),
                'value' => \App\Models\Task::where('user_id', $uid)
                    ->whereNotIn('status', ['completed','cancelled'])
                    ->whereNotNull('due_date')
                    ->where('due_date', '<', today())
                    ->count(),
                'icon'  => 'fas fa-exclamation-triangle',
                'icon_bg' => 'rgba(254,44,85,.12)',
                'icon_color' => '#fe2c55',
                'color' => 'sc-kick-late',
            ],
            [
                'label' => __('tasks.stat_due_today'),
                'value' => \App\Models\Task::where('user_id', $uid)
                    ->whereNotIn('status', ['completed','cancelled'])
                    ->whereNotNull('due_date')
                    ->whereDate('due_date', today())
                    ->count(),
                'icon'  => 'fas fa-calendar-day',
                'icon_bg' => 'rgba(245,158,11,.12)',
                'icon_color' => '#f59e0b',
                'color' => 'sc-kick-today',
            ],
        ];
    @endphp

    <div style="display:grid;grid-template-columns:repeat(6,1fr);gap:12px;margin-bottom:24px;"
         class="stats-grid-tasks">
        @foreach($stats as $s)
        <div class="sc-card sc-card-kick sc-stat-kick starchi-kick-card">
            <div class="sc-stat-icon" style="background:{{ $s['icon_bg'] }};color:{{ $s['icon_color'] }};">
                <i class="{{ $s['icon'] }}"></i>
            </div>
            <div class="sc-stat-label">{{ $s['label'] }}</div>
            <div class="sc-stat-value {{ $s['color'] }}">{{ $s['value'] }}</div>
        </div>
        @endforeach
    </div>

    {{-- ── PowerGrid table ───────────────────────────────────────── --}}
    <livewire:tasks.user-tasks-table />
    <livewire:app.task-modal />
    </div>

</x-layouts::app>

<style>
@media (max-width: 900px) {
    .stats-grid-tasks { grid-template-columns: repeat(3, 1fr) !important; }
}
@media (max-width: 540px) {
    .stats-grid-tasks { grid-template-columns: repeat(2, 1fr) !important; }
}
</style>
