<x-layouts::admin :title="__('admin_pages.notes_index')">

    <div class="sa-page-header">
        <div class="sa-page-header-left">
            <h1>{{ __('admin_ui.notes.heading') }}</h1>
            <p>{{ __('admin_ui.notes.description') }}</p>
        </div>
        <div class="sa-page-header-right flex items-center gap-2">
            <button onclick="Livewire.dispatch('openAdminNotesCalendar')" class="inline-flex items-center gap-1.5 h-9 px-3 bg-zinc-100 hover:bg-zinc-200 text-zinc-700 text-sm font-medium rounded-lg transition dark:bg-zinc-800 dark:hover:bg-zinc-700 dark:text-zinc-200 border border-zinc-200 dark:border-zinc-700">
                <i class="fas fa-calendar-alt"></i>
                {{ __('admin_ui.notes.calendar.open') }}
            </button>
        </div>
    </div>

    @php
        $stats = [
            ['label' => __('admin_ui.notes.stats.total'), 'value' => \App\Models\Note::count(), 'color' => 'text-zinc-700 dark:text-zinc-200'],
            ['label' => __('admin_ui.notes.stats.with_content'), 'value' => \App\Models\Note::whereNotNull('content')->where('content', '!=', '')->count(), 'color' => 'text-cyan-600 dark:text-cyan-400'],
            ['label' => __('admin_ui.notes.stats.without_content'), 'value' => \App\Models\Note::where(function ($q) { $q->whereNull('content')->orWhere('content', ''); })->count(), 'color' => 'text-slate-600 dark:text-slate-400'],
            ['label' => __('admin_ui.notes.stats.indigo'), 'value' => \App\Models\Note::where('color', '#6366f1')->count(), 'color' => 'text-indigo-600 dark:text-indigo-400'],
            ['label' => __('admin_ui.notes.stats.green'), 'value' => \App\Models\Note::where('color', '#22c55e')->count(), 'color' => 'text-emerald-600 dark:text-emerald-400'],
            ['label' => __('admin_ui.notes.stats.red'), 'value' => \App\Models\Note::where('color', '#ef4444')->count(), 'color' => 'text-red-600 dark:text-red-400'],
            ['label' => __('admin_ui.notes.stats.important_date'), 'value' => \App\Models\Note::whereNotNull('important_date')->count(), 'color' => 'text-emerald-600 dark:text-emerald-400'],
        ];
    @endphp

    <div class="sa-stats-grid mb-6">
        @foreach($stats as $s)
            <div class="sa-stat-card">
                <div class="sa-stat-label">{{ $s['label'] }}</div>
                <div class="sa-stat-value {{ $s['color'] }}">{{ $s['value'] }}</div>
            </div>
        @endforeach
    </div>

    <livewire:admin.notes-table />
    <livewire:admin.note-modal />
    <livewire:admin.notes-calendar-modal />
    <livewire:admin.notes-import-modal />

</x-layouts::admin>
