<?php

namespace App\Livewire\App;

use App\Livewire\Concerns\HasStarchoCrudActions;
use App\Models\Note;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
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

    public string $tableName = 'notes-table';

    #[Url]
    public string $filterColor = '';

    public function setUp(): array
    {
        return [
            PowerGrid::header()
                ->showSearchInput()
                ->showToggleColumns()
                ->includeViewOnTop('notes.pg-header'),
            PowerGrid::footer()
                ->showPerPage(15)
                ->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        return Note::query()
            ->where('user_id', Auth::id())
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
            ->add('created_at_fmt', fn (Note $n) => Carbon::parse($n->created_at)->format('d/m/Y'));
    }

    public function columns(): array
    {
        return [
            Column::make(__('notes.col_id'), 'id')->sortable(),
            Column::make(__('notes.col_title'), 'title')->sortable()->searchable(),
            Column::make(__('notes.col_content'), 'content_excerpt', 'content')->searchable(),
            Column::make(__('notes.col_color'), 'color_label', 'color')->sortable(),
            Column::make(__('notes.col_important_date'), 'important_date_fmt', 'important_date')->sortable(),
            Column::make(__('notes.col_created'), 'created_at_fmt', 'created_at')->sortable(),
            Column::action(__('notes.col_actions')),
        ];
    }

    public function filters(): array
    {
        return [];
    }

    public function actions(Note $row): array
    {
        return $this->starchoCrudActions($row, [
            'editEvent' => 'openNote',
            'deleteEvent' => 'deleteNote',
            'tableName' => 'app.notes-table',
            'deleteLabelField' => 'title',
        ]);
    }

    #[On('deleteNote')]
    public function deleteNote(int $id): void
    {
        Note::where('id', $id)->where('user_id', Auth::id())->delete();
        $this->dispatch('pg:eventRefresh-' . $this->tableName);
        $this->dispatch('notes-updated');
    }

    private function colorLabel(string $color): string
    {
        return match (strtolower($color)) {
            '#6366f1' => __('notes.color_indigo'),
            '#22c55e' => __('notes.color_green'),
            '#f59e0b' => __('notes.color_amber'),
            '#ef4444' => __('notes.color_red'),
            '#06b6d4' => __('notes.color_cyan'),
            '#a855f7' => __('notes.color_purple'),
            '#e11d48' => __('notes.color_rose'),
            '#64748b' => __('notes.color_slate'),
            default => $color,
        };
    }
}
