<?php

namespace App\Livewire\Admin;

use App\Exports\AdminTasksExport;
use App\Livewire\Concerns\DispatchesStarchoNotify;
use App\Livewire\Concerns\HasStarchoCrudActions;
use App\Models\AppSetting;
use App\Models\Task;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Maatwebsite\Excel\Facades\Excel;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class TasksTable extends PowerGridComponent
{
    use DispatchesStarchoNotify;
    use HasStarchoCrudActions;

    public string $tableName = 'tasks-table';

    #[Url]
    public string $filterStatus = '';

    #[Url]
    public string $filterPriority = '';

    public function setUp(): array
    {
        $this->showCheckBox();
        $this->persist(['columns'], 'admin');

        return [
            PowerGrid::header()
                ->showSearchInput()
                ->showToggleColumns()
                ->includeViewOnTop('admin.tasks.pg-header'),
            PowerGrid::footer()
                ->showPerPage(15)
                ->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        return Task::query()
            ->with('assignedUser', 'creator')
            ->withoutTrashed()
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterPriority, fn($q) => $q->where('priority', $this->filterPriority));
    }

    public function fields(): PowerGridFields
    {
        $statusLabels = [
            'pending' => __('admin_ui.tasks.status.pending'),
            'in_progress' => __('admin_ui.tasks.status.in_progress'),
            'completed' => __('admin_ui.tasks.status.completed'),
            'cancelled' => __('admin_ui.tasks.status.cancelled'),
        ];

        $priorityLabels = [
            'low' => __('admin_ui.tasks.priority.low'),
            'medium' => __('admin_ui.tasks.priority.medium'),
            'high' => __('admin_ui.tasks.priority.high'),
            'urgent' => __('admin_ui.tasks.priority.urgent'),
        ];

        return PowerGrid::fields()
            ->add('id')
            ->add('title')
            ->add('status_label', fn (Task $t) => $statusLabels[$t->status] ?? $t->status)
            ->add('priority_label', fn (Task $t) => $priorityLabels[$t->priority] ?? $t->priority)
            ->add('due_date_formatted', fn (Task $t) => $t->due_date?->format('d/m/Y') ?? '—')
            ->add('assigned_name', fn (Task $t) => $t->assignedUser?->name ?? __('admin_ui.tasks.form.unassigned'))
            ->add('created_at_formatted', fn (Task $t) => Carbon::parse($t->created_at)->format('d/m/Y'));
    }

    public function columns(): array
    {
        return [
            Column::make(__('admin_ui.tasks.columns.id'), 'id')->sortable()->hidden(),
            Column::make(__('admin_ui.tasks.columns.title'), 'title')->sortable()->searchable(),
            Column::make(__('admin_ui.tasks.columns.status'), 'status_label'),
            Column::make(__('admin_ui.tasks.columns.priority'), 'priority_label'),
            Column::make(__('admin_ui.tasks.columns.due_date'), 'due_date_formatted', 'due_date')->sortable(),
            Column::make(__('admin_ui.tasks.columns.assigned_to'), 'assigned_name'),
            Column::make(__('admin_ui.tasks.columns.created'), 'created_at_formatted', 'created_at')->sortable(),
            Column::action(__('admin_ui.tasks.columns.actions')),
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
            'deleteEvent' => 'deleteTask',
            'tableName' => 'admin.tasks-table',
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
            $this->notifyWarning(__('admin_ui.tasks.notify.no_selection'));
            return null;
        }

        $this->clearSelection();

        return Excel::download(
            new AdminTasksExport($selectedIds),
            'admin-tasks-selected-' . now()->format('Ymd-His') . '.xlsx'
        );
    }

    public function deleteSelected(): void
    {
        $selectedIds = $this->selectedTaskIds();

        if ($selectedIds === []) {
            $this->notifyWarning(__('admin_ui.tasks.notify.no_selection'));
            return;
        }

        $tasks = Task::query()
            ->whereIn('id', $selectedIds)
            ->get();

        if ($tasks->isEmpty()) {
            $this->clearSelection();
            $this->notifyWarning(__('admin_ui.tasks.notify.no_selection'));
            return;
        }

        $deletedCount = 0;

        foreach ($tasks as $task) {
            $task->delete();
            $deletedCount++;
        }

        $this->clearSelection();

        $this->notifyWarning(__('admin_ui.tasks.notify.bulk_deleted', ['count' => $deletedCount]));
        $this->dispatch('pg:eventRefresh-' . $this->tableName);
    }

    #[On('deleteTask')]
    public function deleteTask(int $id): void
    {
        $task = Task::find($id);

        if (! $task) {
            $this->notifyCrud('tasks', 'not_found');
            return;
        }

        $task->delete();
        $this->notifyCrud('tasks', 'deleted');
        $this->dispatch('pg:eventRefresh-' . $this->tableName);
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

    public function toggleFeature(): void
    {
        $current = AppSetting::get('tasks_enabled', '1');
        AppSetting::set('tasks_enabled', $current === '1' ? '0' : '1');
        $this->dispatch('$refresh');
    }
}
