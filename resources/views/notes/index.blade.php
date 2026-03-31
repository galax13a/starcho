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

        <livewire:app.notes-stats />

        <livewire:app.notes-table />
        <livewire:app.note-modal />
        <livewire:app.notes-import-modal />
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
