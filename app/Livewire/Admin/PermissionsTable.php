<?php

namespace App\Livewire\Admin;

use App\Exports\AdminPermissionsExport;
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
use Spatie\Permission\Models\Permission;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class PermissionsTable extends PowerGridComponent
{
    use DispatchesStarchoNotify;
    use HasStarchoCrudActions;

    public string $tableName = 'permissions-table';

    public function setUp(): array
    {
        $this->showCheckBox();
        $this->persist(['columns'], 'admin');

        return [
            PowerGrid::header()
                ->showSearchInput()
                ->showToggleColumns()
                ->includeViewOnTop('admin.permissions.pg-header'),
            PowerGrid::footer()
                ->showPerPage(15)
                ->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        return Permission::query()->withCount('roles');
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('name')
            ->add('roles_count')
            ->add('guard_name')
            ->add('created_at_formatted', fn (Permission $perm) => Carbon::parse($perm->created_at)->format('d/m/Y'));
    }

    public function columns(): array
    {
        return [
            Column::make(__('admin_ui.permissions.columns.id'), 'id')->sortable(),
            Column::make(__('admin_ui.permissions.columns.permission'), 'name')->sortable()->searchable(),
            Column::make(__('admin_ui.permissions.columns.guard'), 'guard_name')->sortable()->hidden(),
            Column::make(__('admin_ui.permissions.columns.roles'), 'roles_count')->sortable(),
            Column::make(__('admin_ui.permissions.columns.created'), 'created_at_formatted', 'created_at')->sortable(),
            Column::action(__('admin_ui.permissions.columns.actions')),
        ];
    }

    public function filters(): array
    {
        return [];
    }

    public function actions(Permission $row): array
    {
        return $this->starchoCrudActions($row, [
            'editEvent' => 'openPermission',
            'deleteEvent' => 'deletePermission',
            'tableName' => 'admin.permissions-table',
            'deleteLabelField' => 'name',
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
        $selectedIds = $this->selectedPermissionIds();

        if ($selectedIds === []) {
            $this->notifyWarning(__('admin_ui.permissions.notify.no_selection'));
            return null;
        }

        $this->clearSelection();

        return Excel::download(
            new AdminPermissionsExport($selectedIds),
            'admin-permissions-selected-' . now()->format('Ymd-His') . '.xlsx'
        );
    }

    public function deleteSelected(): void
    {
        $selectedIds = $this->selectedPermissionIds();

        if ($selectedIds === []) {
            $this->notifyWarning(__('admin_ui.permissions.notify.no_selection'));
            return;
        }

        $permissions = Permission::query()
            ->whereIn('id', $selectedIds)
            ->get();

        if ($permissions->isEmpty()) {
            $this->clearSelection();
            $this->notifyWarning(__('admin_ui.permissions.notify.no_selection'));
            return;
        }

        $deletedCount = 0;

        foreach ($permissions as $permission) {
            $permission->delete();
            $deletedCount++;
        }

        $this->clearSelection();
        $this->notifyWarning(__('admin_ui.permissions.notify.bulk_deleted', ['count' => $deletedCount]));
        $this->dispatch('pg:eventRefresh-' . $this->tableName);
    }

    #[On('deletePermission')]
    public function deletePermission(int $id): void
    {
        $permission = Permission::find($id);

        if (! $permission) {
            $this->notifyCrud('permissions', 'not_found');
            return;
        }

        $permission->delete();
        $this->notifyCrud('permissions', 'deleted');
        $this->dispatch('pg:eventRefresh-' . $this->tableName);
    }

    private function selectedPermissionIds(): array
    {
        return collect($this->checkboxValues)
            ->map(static fn (string|int $id): int => (int) $id)
            ->filter(static fn (int $id): bool => $id > 0)
            ->unique()
            ->values()
            ->all();
    }
}
