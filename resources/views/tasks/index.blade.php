<x-layouts::app :title="__('tasks.page_title')">
    <div class="sa-page starchi-kick">
        <div class="sa-page-header starchi-kick-header">
            <div class="sa-page-header-left">
                <h1>{{ __('tasks.page_title') }}</h1>
                <p>{{ __('tasks.page_subtitle') }}</p>
            </div>
            <div class="sa-page-header-right">
                <x-starcho-btn-kick
                    onclick="Livewire.dispatch('openTask', {id:0})"
                    icon="fas fa-plus"
                    :label="__('tasks.new_task')"
                    class="starchi-kick-btn"
                />
            </div>
        </div>

    <livewire:app.tasks-stats />

    {{-- ── PowerGrid table ───────────────────────────────────────── --}}
    <livewire:tasks.user-tasks-table />
    <livewire:app.task-modal />
    <livewire:app.tasks-import-modal />
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
