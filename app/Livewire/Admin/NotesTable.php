<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\HasStarchoCrudActions;
use App\Models\Note;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class NotesTable extends PowerGridComponent
{
    use HasStarchoCrudActions;

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
        return $this->starchoCrudActions($row, [
            'editEvent' => 'openAdminNote',
            'deleteEvent' => 'deleteAdminNote',
            'tableName' => 'admin.notes-table',
            'deleteLabelField' => 'title',
        ]);
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
