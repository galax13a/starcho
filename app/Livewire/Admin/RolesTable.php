<?php

namespace App\Livewire\Admin;

use App\Exports\AdminRolesExport;
use App\Livewire\Concerns\DispatchesStarchoNotify;
use App\Livewire\Concerns\HasStarchoCrudActions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Livewire\Attributes\On;
use Maatwebsite\Excel\Facades\Excel;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class RolesTable extends PowerGridComponent
{
    use DispatchesStarchoNotify;
    use HasStarchoCrudActions;

    public string $tableName = 'roles-table';

    public function setUp(): array
    {
        $this->showCheckBox();
        $this->persist(['columns'], 'admin');

        return [
            PowerGrid::header()
                ->showSearchInput()
                ->showToggleColumns()
                ->includeViewOnTop('admin.roles.pg-header'),
            PowerGrid::footer()
                ->showPerPage(10)
                ->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        return Role::query()->withCount('permissions');
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('name')
            ->add('permissions_count')
            ->add('guard_name')
            ->add('created_at_formatted', fn (Role $role) => Carbon::parse($role->created_at)->format('d/m/Y'));
    }

    public function columns(): array
    {
        return [
            Column::make(__('admin_ui.roles.columns.id'), 'id')->sortable()->hidden(),
            Column::make(__('admin_ui.roles.columns.name'), 'name')->sortable()->searchable(),
            Column::make(__('admin_ui.roles.columns.guard'), 'guard_name')->sortable()->hidden(),
            Column::make(__('admin_ui.roles.columns.permissions'), 'permissions_count')->sortable(),
            Column::make(__('admin_ui.roles.columns.created'), 'created_at_formatted', 'created_at')->sortable(),
            Column::action(__('admin_ui.roles.columns.actions')),
        ];
    }

    public function filters(): array
    {
        return [];
    }

    public function actions(Role $row): array
    {
        return $this->starchoCrudActions($row, [
            'editEvent' => 'openRole',
            'deleteEvent' => 'deleteRole',
            'tableName' => 'admin.roles-table',
            'deleteLabelField' => 'name',
            'showDelete' => fn (Role $role) => $role->name !== 'admin',
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
        $selectedIds = $this->selectedRoleIds();

        if ($selectedIds === []) {
            $this->notifyWarning(__('admin_ui.roles.notify.no_selection'));
            return null;
        }

        $this->clearSelection();

        return Excel::download(
            new AdminRolesExport($selectedIds),
            'admin-roles-selected-' . now()->format('Ymd-His') . '.xlsx'
        );
    }

    public function deleteSelected(): void
    {
        $selectedIds = $this->selectedRoleIds();

        if ($selectedIds === []) {
            $this->notifyWarning(__('admin_ui.roles.notify.no_selection'));
            return;
        }

        $roles = Role::query()
            ->whereIn('id', $selectedIds)
            ->get();

        if ($roles->isEmpty()) {
            $this->clearSelection();
            $this->notifyWarning(__('admin_ui.roles.notify.no_selection'));
            return;
        }

        $deletedCount = 0;

        foreach ($roles as $role) {
            if ($role->name === 'admin') {
                continue;
            }

            $role->delete();
            $deletedCount++;
        }

        $this->clearSelection();

        if ($deletedCount === 0) {
            $this->notifyWarning(__('admin_ui.roles.notify.cannot_delete_admin'));
            return;
        }

        $this->notifyWarning(__('admin_ui.roles.notify.bulk_deleted', ['count' => $deletedCount]));
        $this->dispatch('pg:eventRefresh-' . $this->tableName);
    }

    #[On('deleteRole')]
    public function deleteRole(int $id): void
    {
        $role = Role::find($id);

        if (! $role || $role->name === 'admin') {
            $this->notifyCrud('roles', 'cannot_delete_admin');
            return;
        }

        $role->delete();

        $this->notifyCrud('roles', 'deleted');

        $this->dispatch('pg:eventRefresh-' . $this->tableName);
    }

    private function selectedRoleIds(): array
    {
        return collect($this->checkboxValues)
            ->map(static fn (string|int $id): int => (int) $id)
            ->filter(static fn (int $id): bool => $id > 0)
            ->unique()
            ->values()
            ->all();
    }
}
