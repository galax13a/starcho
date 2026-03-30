<?php

namespace App\Livewire\Admin;

use App\Models\Note;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class NotesTable extends PowerGridComponent
{
    public string $tableName = 'admin-notes-table';

    #[Url]
    public string $filterColor = '';

    public function setUp(): array
    {
        return [
            PowerGrid::header()
                ->showSearchInput()
                ->showToggleColumns()
                ->includeViewOnTop('admin.notes.pg-header'),
            PowerGrid::footer()
                ->showPerPage(15)
                ->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        return Note::query()
            ->with('creator')
            ->when($this->filterColor, fn ($q) => $q->where('color', $this->filterColor));
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('title')
            ->add('content_excerpt', fn (Note $n) => Str::limit((string) ($n->content ?? ''), 80) ?: '—')
            ->add('color_label', fn (Note $n) => $this->colorLabel($n->color))
            ->add('important_date_fmt', fn (Note $n) => $n->important_date?->format('d/m/Y') ?? '—')
            ->add('creator_name', fn (Note $n) => $n->creator?->name ?? '—')
            ->add('created_at_fmt', fn (Note $n) => Carbon::parse($n->created_at)->format('d/m/Y'));
    }

    public function columns(): array
    {
        return [
            Column::make(__('admin_ui.notes.columns.id'), 'id')->sortable(),
            Column::make(__('admin_ui.notes.columns.title'), 'title')->sortable()->searchable(),
            Column::make(__('admin_ui.notes.columns.content'), 'content_excerpt', 'content')->searchable(),
            Column::make(__('admin_ui.notes.columns.color'), 'color_label', 'color')->sortable(),
            Column::make(__('admin_ui.notes.columns.important_date'), 'important_date_fmt', 'important_date')->sortable(),
            Column::make(__('admin_ui.notes.columns.created_by'), 'creator_name'),
            Column::make(__('admin_ui.notes.columns.date'), 'created_at_fmt', 'created_at')->sortable(),
            Column::action(__('admin_ui.notes.columns.actions')),
        ];
    }

    public function filters(): array
    {
        return [];
    }

    public function actions(Note $row): array
    {
        $iconEdit = '<svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125 16.875 4.5"/></svg>';
        $iconDelete = '<svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>';

        return [
            Button::add('edit')
                ->slot($iconEdit)
                ->dispatch('openAdminNote', ['id' => $row->id])
                ->class('inline-flex items-center justify-center size-8 rounded-lg border border-zinc-200 text-zinc-600 hover:text-blue-600 hover:border-blue-300 hover:bg-blue-50 transition dark:border-zinc-700 dark:text-zinc-300 dark:hover:text-blue-400 dark:hover:bg-blue-900/30'),
            Button::add('delete')
                ->slot($iconDelete)
                ->attributes(['onclick' => "starchoDelete({$row->id},'" . addslashes($row->title) . "','deleteAdminNote','admin.admin-notes-table')"])
                ->class('inline-flex items-center justify-center size-8 rounded-lg border border-zinc-200 text-zinc-600 hover:text-red-600 hover:border-red-300 hover:bg-red-50 transition dark:border-zinc-700 dark:text-zinc-300 dark:hover:text-red-400 dark:hover:bg-red-900/30'),
        ];
    }

    #[On('deleteAdminNote')]
    public function deleteAdminNote(int $id): void
    {
        Note::find($id)?->delete();
        $this->dispatch('pg:eventRefresh-' . $this->tableName);
    }

    private function colorLabel(string $color): string
    {
        return match (strtolower($color)) {
            '#6366f1' => __('admin_ui.notes.colors.indigo'),
            '#22c55e' => __('admin_ui.notes.colors.green'),
            '#f59e0b' => __('admin_ui.notes.colors.amber'),
            '#ef4444' => __('admin_ui.notes.colors.red'),
            '#06b6d4' => __('admin_ui.notes.colors.cyan'),
            '#a855f7' => __('admin_ui.notes.colors.purple'),
            '#e11d48' => __('admin_ui.notes.colors.rose'),
            '#64748b' => __('admin_ui.notes.colors.slate'),
            default => $color,
        };
    }
}
