<x-layouts::app :title="'Mis Tareas'">
    <div class="sa-page">
        <div class="sa-page-header">
            <div class="sa-page-header-left">
                <h1>Mis Tareas</h1>
                <p>Gestiona tus tareas personales y de equipo con estilo admin en /app.</p>
            </div>
            <div class="sa-page-header-right">
                <button onclick="Livewire.dispatch('openTask', {id:0})" class="sa-btn sa-btn-primary">
                    <i class="fas fa-plus"></i> Nueva Tarea
                </button>
            </div>
        </div>
    </div>

    {{-- ── TikTok stat cards ────────────────────────────────────── --}}
    @php
        $uid = auth()->id();
        $stats = [
            [
                'label' => 'Total',
                'value' => \App\Models\Task::where('user_id', $uid)->count(),
                'icon'  => 'fas fa-layer-group',
                'icon_bg' => 'rgba(255,255,255,.07)',
                'icon_color' => '#fff',
                'color' => 'sc-tt-total',
            ],
            [
                'label' => 'Pendientes',
                'value' => \App\Models\Task::where('user_id', $uid)->where('status', 'pending')->count(),
                'icon'  => 'fas fa-clock',
                'icon_bg' => 'rgba(160,160,160,.12)',
                'icon_color' => '#a0a0a0',
                'color' => 'sc-tt-pending',
            ],
            [
                'label' => 'En progreso',
                'value' => \App\Models\Task::where('user_id', $uid)->where('status', 'in_progress')->count(),
                'icon'  => 'fas fa-spinner',
                'icon_bg' => 'rgba(37,244,238,.1)',
                'icon_color' => '#25f4ee',
                'color' => 'sc-tt-progress',
            ],
            [
                'label' => 'Completadas',
                'value' => \App\Models\Task::where('user_id', $uid)->where('status', 'completed')->count(),
                'icon'  => 'fas fa-check-circle',
                'icon_bg' => 'rgba(83,252,24,.1)',
                'icon_color' => '#53fc18',
                'color' => 'sc-tt-done',
            ],
            [
                'label' => 'Vencidas',
                'value' => \App\Models\Task::where('user_id', $uid)
                    ->whereNotIn('status', ['completed','cancelled'])
                    ->whereNotNull('due_date')
                    ->where('due_date', '<', today())
                    ->count(),
                'icon'  => 'fas fa-exclamation-triangle',
                'icon_bg' => 'rgba(254,44,85,.12)',
                'icon_color' => '#fe2c55',
                'color' => 'sc-tt-late',
            ],
            [
                'label' => 'Vencen hoy',
                'value' => \App\Models\Task::where('user_id', $uid)
                    ->whereNotIn('status', ['completed','cancelled'])
                    ->whereNotNull('due_date')
                    ->whereDate('due_date', today())
                    ->count(),
                'icon'  => 'fas fa-calendar-day',
                'icon_bg' => 'rgba(245,158,11,.12)',
                'icon_color' => '#f59e0b',
                'color' => 'sc-tt-today',
            ],
        ];
    @endphp

    <div style="display:grid;grid-template-columns:repeat(6,1fr);gap:12px;margin-bottom:24px;"
         class="stats-grid-tasks">
        @foreach($stats as $s)
        <div class="sc-card sc-card-tt sc-stat-tt">
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
    <livewire:admin.task-modal />

</x-layouts::app>

<style>
@media (max-width: 900px) {
    .stats-grid-tasks { grid-template-columns: repeat(3, 1fr) !important; }
}
@media (max-width: 540px) {
    .stats-grid-tasks { grid-template-columns: repeat(2, 1fr) !important; }
}
</style>
