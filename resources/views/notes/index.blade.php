<x-layouts::app :title="__('notes.page_title')">
    <div class="sa-page starcho-tiktok">
        <div class="sa-page-header starcho-tiktok-header">
            <div class="sa-page-header-left">
                <h1>{{ __('notes.page_title') }}</h1>
                <p>{{ __('notes.page_subtitle') }}</p>
            </div>
            <div class="sa-page-header-right">
                <button onclick="Livewire.dispatch('openNotesCalendar')" class="sc-btn sc-btn-tt sc-btn-ghost">
                    <i class="fas fa-calendar-alt"></i> {{ __('notes.show_calendar') }}
                </button>
                <button onclick="Livewire.dispatch('openNote', {id:0})" class="sc-btn sc-btn-tt starcho-tiktok-btn">
                    <i class="fas fa-plus"></i> {{ __('notes.new_note') }}
                </button>
            </div>
        </div>

        @php
            $uid = auth()->id();
            $stats = [
                [
                    'label' => __('notes.stat_total'),
                    'value' => \App\Models\Note::where('user_id', $uid)->count(),
                    'icon' => 'fas fa-note-sticky',
                    'icon_bg' => 'rgba(254,44,85,.12)',
                    'icon_color' => '#fe2c55',
                    'color' => 'sc-tt-total',
                ],
                [
                    'label' => __('notes.stat_with_content'),
                    'value' => \App\Models\Note::where('user_id', $uid)->whereNotNull('content')->where('content', '!=', '')->count(),
                    'icon' => 'fas fa-align-left',
                    'icon_bg' => 'rgba(37,244,238,.12)',
                    'icon_color' => '#25f4ee',
                    'color' => 'sc-tt-progress',
                ],
                [
                    'label' => __('notes.stat_without_content'),
                    'value' => \App\Models\Note::where('user_id', $uid)->where(function ($q) {
                        $q->whereNull('content')->orWhere('content', '');
                    })->count(),
                    'icon' => 'fas fa-minus-circle',
                    'icon_bg' => 'rgba(170,170,170,.12)',
                    'icon_color' => '#aaaaaa',
                    'color' => 'sc-tt-pending',
                ],
                [
                    'label' => __('notes.stat_indigo'),
                    'value' => \App\Models\Note::where('user_id', $uid)->where('color', '#6366f1')->count(),
                    'icon' => 'fas fa-circle',
                    'icon_bg' => 'rgba(99,102,241,.14)',
                    'icon_color' => '#818cf8',
                    'color' => 'sc-tt-total',
                ],
                [
                    'label' => __('notes.stat_green'),
                    'value' => \App\Models\Note::where('user_id', $uid)->where('color', '#22c55e')->count(),
                    'icon' => 'fas fa-circle',
                    'icon_bg' => 'rgba(34,197,94,.12)',
                    'icon_color' => '#4ade80',
                    'color' => 'sc-tt-done',
                ],
                [
                    'label' => __('notes.stat_red'),
                    'value' => \App\Models\Note::where('user_id', $uid)->where('color', '#ef4444')->count(),
                    'icon' => 'fas fa-circle',
                    'icon_bg' => 'rgba(254,44,85,.12)',
                    'icon_color' => '#fe2c55',
                    'color' => 'sc-tt-late',
                ],
                [
                    'label' => __('notes.stat_important_date'),
                    'value' => \App\Models\Note::where('user_id', $uid)->whereNotNull('important_date')->count(),
                    'icon' => 'fas fa-calendar-check',
                    'icon_bg' => 'rgba(245,158,11,.14)',
                    'icon_color' => '#f59e0b',
                    'color' => 'sc-tt-today',
                ],
            ];
        @endphp

        <div style="display:grid;grid-template-columns:repeat(7,1fr);gap:12px;margin-bottom:24px;" class="stats-grid-notes">
            @foreach($stats as $s)
                <div class="sc-card sc-card-tt sc-stat-tt starcho-tiktok-card">
                    <div class="sc-stat-icon" style="background:{{ $s['icon_bg'] }};color:{{ $s['icon_color'] }};">
                        <i class="{{ $s['icon'] }}"></i>
                    </div>
                    <div class="sc-stat-label">{{ $s['label'] }}</div>
                    <div class="sc-stat-value {{ $s['color'] }}">{{ $s['value'] }}</div>
                </div>
            @endforeach
        </div>

        <livewire:app.notes-table />
        <livewire:app.note-modal />
        <livewire:app.notes-calendar-modal />
    </div>
</x-layouts::app>

<style>
@media (max-width: 900px) {
    .stats-grid-notes { grid-template-columns: repeat(3, 1fr) !important; }
}
@media (max-width: 540px) {
    .stats-grid-notes { grid-template-columns: repeat(2, 1fr) !important; }
}
</style>
