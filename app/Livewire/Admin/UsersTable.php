<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\HasStarchoCrudActions;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Livewire\Attributes\On;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class UsersTable extends PowerGridComponent
{
    use HasStarchoCrudActions;

    public string $tableName = 'users-table';

    public function setUp(): array
    {
        return [
            PowerGrid::header()
                ->showSearchInput()
                ->showToggleColumns()
                ->includeViewOnTop('admin.users.pg-header'),
            PowerGrid::footer()
                ->showPerPage(15)
                ->showRecordCount(),
            PowerGrid::exportable('usuarios-export')
                ->type(\PowerComponents\LivewirePowerGrid\Components\SetUp\Exportable::TYPE_XLS,
                       \PowerComponents\LivewirePowerGrid\Components\SetUp\Exportable::TYPE_CSV),
        ];
    }

    public function datasource(): Builder
    {
        return User::query()->with('roles');
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('name')
            ->add('email')
            ->add('roles_list', fn (User $user) => $user->roles->pluck('name')->join(', ') ?: __('admin_ui.users.no_role'))
            ->add('email_verified_label', fn (User $user) => $user->email_verified_at ? __('admin_ui.users.verified') : __('admin_ui.users.unverified'))
            ->add('created_at_formatted', fn (User $user) => Carbon::parse($user->created_at)->format('d/m/Y'));
    }

    public function columns(): array
    {
        return [
            Column::make(__('admin_ui.users.columns.id'), 'id')->sortable(),
            Column::make(__('admin_ui.users.columns.name'), 'name')->sortable()->searchable(),
            Column::make(__('admin_ui.users.columns.email'), 'email')->sortable()->searchable(),
            Column::make(__('admin_ui.users.columns.roles'), 'roles_list'),
            Column::make(__('admin_ui.users.columns.verification'), 'email_verified_label'),
            Column::make(__('admin_ui.users.columns.registered'), 'created_at_formatted', 'created_at')->sortable(),
            Column::action(__('admin_ui.users.columns.actions')),
        ];
    }

    public function filters(): array
    {
        return [];
    }

    public function actions(User $row): array
    {
        return $this->starchoCrudActions($row, [
            'editEvent' => 'openUser',
            'deleteEvent' => 'deleteUser',
            'tableName' => 'admin.users-table',
            'deleteLabelField' => 'name',
        ]);
    }

    #[On('deleteUser')]
    public function deleteUser(int $id): void
    {
        User::find($id)?->delete();
        $this->dispatch('pg:eventRefresh-' . $this->tableName);
    }
}
