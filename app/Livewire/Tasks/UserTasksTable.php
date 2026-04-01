<?php

namespace App\Livewire\Tasks;

use App\Exports\AppTasksExport;
use App\Livewire\Concerns\DispatchesStarchoNotify;
use App\Livewire\Concerns\HasStarchoCrudActions;
use App\Models\Task;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Maatwebsite\Excel\Facades\Excel;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class UserTasksTable extends PowerGridComponent
{
    use DispatchesStarchoNotify;
    use HasStarchoCrudActions;

    public string $tableName = 'user-tasks-table';

    #[Url]
    public string $filterStatus = '';

    #[Url]
    public string $filterPriority = '';

    public function setUp(): array
    {
        $this->showCheckBox();
        $this->persist(['columns'], 'app');

        return [
            PowerGrid::header()
                ->showSearchInput()
                ->showToggleColumns()
                ->includeViewOnTop('tasks.pg-header'),
            PowerGrid::footer()
                ->showPerPage(15)
                ->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        return Task::query()
            ->with('assignedUser')
            ->withoutTrashed()
            ->where('user_id', Auth::id())
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterPriority, fn($q) => $q->where('priority', $this->filterPriority));
    }

    public function fields(): PowerGridFields
    {
        $statusLabels = [
            'pending' => __('tasks.status_pending'),
            'in_progress' => __('tasks.status_in_progress'),
            'completed' => __('tasks.status_completed'),
            'cancelled' => __('tasks.status_cancelled'),
        ];

        $priorityLabels = [
            'low' => __('tasks.priority_low'),
            'medium' => __('tasks.priority_medium'),
            'high' => __('tasks.priority_high'),
            'urgent' => __('tasks.priority_urgent'),
        ];

        return PowerGrid::fields()
            ->add('id')
            ->add('title')
            ->add('status_label', fn (Task $t) => $statusLabels[$t->status] ?? $t->status)
            ->add('priority_label', fn (Task $t) => $priorityLabels[$t->priority] ?? $t->priority)
            ->add('due_date_formatted', fn (Task $t) => $t->due_date?->format('d/m/Y') ?? '—')
            ->add('assigned_name', fn (Task $t) => $t->assignedUser?->name ?? __('tasks.field_unassigned'))
            ->add('created_at_formatted', fn (Task $t) => Carbon::parse($t->created_at)->format('d/m/Y'));
    }

    public function columns(): array
    {
        return [
            Column::make(__('tasks.col_id'), 'id')->sortable()->hidden(),
            Column::make(__('tasks.col_title'), 'title')->sortable()->searchable(),
            Column::make(__('tasks.col_status'), 'status_label'),
            Column::make(__('tasks.col_priority'), 'priority_label'),
            Column::make(__('tasks.col_due_date'), 'due_date_formatted', 'due_date')->sortable(),
            Column::make(__('tasks.col_assigned_to'), 'assigned_name'),
            Column::make(__('tasks.col_created'), 'created_at_formatted', 'created_at')->sortable(),
            Column::action(__('tasks.col_actions')),
        ];
    }

    public function filters(): array
    {
        return [];
    }

    public function actions(Task $row): array
    {
        return $this->starchoCrudActions($row, [
            'editEvent' => 'openTask',
            'deleteEvent' => 'deleteUserTask',
            'tableName' => 'tasks.user-tasks-table',
            'deleteLabelField' => 'title',
        ]);
    }

    public function clearSelection(): void
    {
        $this->checkboxAll = false;
        $this->checkboxValues = [];
        $this->dispatch('pgBulkActions::clear', $this->tableName);
    }

    public function exportSelected(): BinaryFileResponse|null
    {
        $selectedIds = $this->selectedTaskIds();

        if ($selectedIds === []) {
            $this->notifyWarning(__('tasks.notify.no_selection'));
            return null;
        }

        $this->clearSelection();

        return Excel::download(
            new AppTasksExport((int) Auth::id(), $selectedIds),
            'tasks-selected-' . now()->format('Ymd-His') . '.xlsx'
        );
    }

    public function deleteSelected(): void
    {
        $selectedIds = $this->selectedTaskIds();

        if ($selectedIds === []) {
            $this->notifyWarning(__('tasks.notify.no_selection'));
            return;
        }

        $tasks = Task::query()
            ->where('user_id', Auth::id())
            ->whereIn('id', $selectedIds)
            ->get();

        if ($tasks->isEmpty()) {
            $this->clearSelection();
            $this->notifyWarning(__('tasks.notify.no_selection'));
            return;
        }

        $deletedCount = 0;

        foreach ($tasks as $task) {
            $task->delete();
            $deletedCount++;
        }

        $this->clearSelection();

        $this->notifyWarning(__('tasks.notify.bulk_deleted', ['count' => $deletedCount]));
        $this->dispatch('pg:eventRefresh-' . $this->tableName);
        $this->dispatch('tasks-updated');
    }

    #[On('deleteUserTask')]
    public function deleteUserTask(int $id): void
    {
        $task = Task::where('id', $id)->where('user_id', Auth::id())->first();

        if (! $task) {
            $this->notifyFailure(__('tasks.notify.not_found'));
            return;
        }

        $task->delete();

        $this->notifyWarning(__('tasks.notify.deleted'));
        $this->dispatch('pg:eventRefresh-' . $this->tableName);
        $this->dispatch('tasks-updated');
    }

    private function selectedTaskIds(): array
    {
        return collect($this->checkboxValues)
            ->map(static fn (string|int $id): int => (int) $id)
            ->filter(static fn (int $id): bool => $id > 0)
            ->unique()
            ->values()
            ->all();
    }
}
