<x-layouts::app :title="__('notes.page_title')">
    <div class="sa-page starcho-tiktok">
        <div class="sa-page-header starcho-tiktok-header">
            <div class="sa-page-header-left">
                <h1>{{ __('notes.page_title') }}</h1>
                <p>{{ __('notes.page_subtitle') }}</p>
            </div>
            <div class="sa-page-header-right">
                <x-starcho-btn-tiktok
                    variant="ghost"
                    icon="fas fa-calendar-alt"
                    :label="__('notes.show_calendar')"
                    onclick="Livewire.dispatch('openNotesCalendar')"
                />
                <x-starcho-btn-tiktok
                    class="starcho-tiktok-btn"
                    icon="fas fa-plus"
                    :label="__('notes.new_note')"
                    onclick="Livewire.dispatch('openNote', {id:0})"
                />
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
