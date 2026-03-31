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
            ['label' => __('admin_ui.notes.stats.total'), 'value' => \App\Models\Note::count(), 'tone' => 'default'],
            ['label' => __('admin_ui.notes.stats.with_content'), 'value' => \App\Models\Note::whereNotNull('content')->where('content', '!=', '')->count(), 'tone' => 'cyan'],
            ['label' => __('admin_ui.notes.stats.without_content'), 'value' => \App\Models\Note::where(function ($q) { $q->whereNull('content')->orWhere('content', ''); })->count(), 'tone' => 'slate'],
            ['label' => __('admin_ui.notes.stats.indigo'), 'value' => \App\Models\Note::where('color', '#6366f1')->count(), 'tone' => 'indigo'],
            ['label' => __('admin_ui.notes.stats.green'), 'value' => \App\Models\Note::where('color', '#22c55e')->count(), 'tone' => 'emerald'],
            ['label' => __('admin_ui.notes.stats.red'), 'value' => \App\Models\Note::where('color', '#ef4444')->count(), 'tone' => 'red'],
            ['label' => __('admin_ui.notes.stats.important_date'), 'value' => \App\Models\Note::whereNotNull('important_date')->count(), 'tone' => 'violet'],
        ];
    @endphp

    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-3 mb-6">
        @foreach($stats as $s)
            <x-starcho-card-statsOne
                :label="$s['label']"
                :value="$s['value']"
                :tone="$s['tone']"
            />
        @endforeach
    </div>

    <livewire:admin.notes-table />
    <livewire:admin.note-modal />
    <livewire:admin.notes-calendar-modal />
    <livewire:admin.notes-import-modal />

</x-layouts::admin>
