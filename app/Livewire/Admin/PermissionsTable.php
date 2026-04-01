<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\DispatchesStarchoNotify;
use App\Livewire\Concerns\HasStarchoCrudActions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Livewire\Attributes\On;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use Spatie\Permission\Models\Permission;

final class PermissionsTable extends PowerGridComponent
{
    use DispatchesStarchoNotify;
    use HasStarchoCrudActions;

    public string $tableName = 'permissions-table';

    public function setUp(): array
    {
        return [
            PowerGrid::header()
                ->showSearchInput()
                ->showToggleColumns()
                ->includeViewOnTop('admin.permissions.pg-header'),
            PowerGrid::footer()
                ->showPerPage(15)
                ->showRecordCount(),
            PowerGrid::exportable('permissions-export')
                ->type(\PowerComponents\LivewirePowerGrid\Components\SetUp\Exportable::TYPE_XLS,
                       \PowerComponents\LivewirePowerGrid\Components\SetUp\Exportable::TYPE_CSV),
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
}
