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
use Spatie\Permission\Models\Role;

final class RolesTable extends PowerGridComponent
{
    use DispatchesStarchoNotify;
    use HasStarchoCrudActions;

    public string $tableName = 'roles-table';

    public function setUp(): array
    {
        return [
            PowerGrid::header()
                ->showSearchInput()
                ->showToggleColumns()
                ->includeViewOnTop('admin.roles.pg-header'),
            PowerGrid::footer()
                ->showPerPage(10)
                ->showRecordCount(),
            PowerGrid::exportable('roles-export')
                ->type(\PowerComponents\LivewirePowerGrid\Components\SetUp\Exportable::TYPE_XLS,
                       \PowerComponents\LivewirePowerGrid\Components\SetUp\Exportable::TYPE_CSV),
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
            Column::make(__('admin_ui.roles.columns.id'), 'id')->sortable(),
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
}
