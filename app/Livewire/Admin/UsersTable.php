<?php

namespace App\Livewire\Admin;

use App\Exports\AdminUsersExport;
use App\Livewire\Concerns\DispatchesStarchoNotify;
use App\Livewire\Concerns\HasStarchoCrudActions;
use App\Models\AiPlan;
use App\Models\StoragePlan;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Carbon;
use Livewire\Attributes\On;
use Maatwebsite\Excel\Facades\Excel;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class UsersTable extends PowerGridComponent
{
    use DispatchesStarchoNotify;
    use HasStarchoCrudActions;

    public string $tableName = 'users-table';

    private ?Collection $storagePlansCache = null;
    private ?Collection $aiPlansCache = null;

    private function storagePlans(): Collection
    {
        return $this->storagePlansCache ??= StoragePlan::orderBy('sort_order')->get();
    }

    private function aiPlans(): Collection
    {
        return $this->aiPlansCache ??= AiPlan::orderBy('sort_order')->get();
    }

    public function setUp(): array
    {
        $this->showCheckBox();
        $this->persist(['columns'], 'admin');

        return [
            PowerGrid::header()
                ->showSearchInput()
                ->showToggleColumns()
                ->includeViewOnTop('admin.users.pg-header'),
            PowerGrid::footer()
                ->showPerPage(15)
                ->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        return User::query()->with(['roles', 'storagePlan', 'aiPlan']);
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('name')
            ->add('email')
            ->add('roles_list', fn (User $user) => $user->roles->pluck('name')->join(', ') ?: __('admin_ui.users.no_role'))
            ->add('email_verified_label', fn (User $user) => $user->email_verified_at ? __('admin_ui.users.verified') : __('admin_ui.users.unverified'))
            ->add('storage_plan_label', fn (User $user) => $this->storagePlanSelect($user))
            ->add('ai_plan_label', fn (User $user) => $this->aiPlanSelect($user))
            ->add('storage_usage_label', fn (User $user) => $this->storageUsageBar($user))
            ->add('created_at_formatted', fn (User $user) => Carbon::parse($user->created_at)->format('d/m/Y'));
    }

    private function selectClasses(): string
    {
        return 'h-8 min-w-[150px] rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 '
            . 'text-xs text-zinc-700 dark:text-zinc-300 px-2 pr-7 focus:outline-none focus:ring-2 focus:ring-violet-400/20 focus:border-violet-400 transition';
    }

    private function aiPlanSelect(User $user): string
    {
        $current = (int) ($user->ai_plan_id ?? 0);
        $options = '<option value="">Sin plan IA</option>';

        foreach ($this->aiPlans() as $plan) {
            $selected = $current === (int) $plan->id ? ' selected' : '';
            $price = $plan->is_free ? 'Gratis' : '$' . number_format($plan->monthly_price, 2);
            $options .= '<option value="' . $plan->id . '"' . $selected . '>' . e($plan->name) . ' · ' . e($price) . '</option>';
        }

        return '<select wire:change="changeAiPlan(' . $user->id . ', $event.target.value)" class="' . $this->selectClasses() . '">'
            . $options . '</select>';
    }

    private function storagePlanSelect(User $user): string
    {
        $current = (int) ($user->storage_plan_id ?? 0);
        $options = '<option value="">' . e(__('admin_ui.users.no_plan')) . '</option>';

        foreach ($this->storagePlans() as $plan) {
            $selected = $current === (int) $plan->id ? ' selected' : '';
            $options .= '<option value="' . $plan->id . '"' . $selected . '>' . e($plan->name) . ' · ' . e($plan->limitLabel()) . '</option>';
        }

        return '<select wire:change="changeStoragePlan(' . $user->id . ', $event.target.value)" class="' . $this->selectClasses() . '">'
            . $options . '</select>';
    }

    public function changeStoragePlan(int $userId, string $planId): void
    {
        $user = User::find($userId);

        if (! $user) {
            $this->notifyFailure('Usuario no encontrado.');
            return;
        }

        $newPlanId = $planId !== '' ? (int) $planId : null;

        if ($newPlanId !== null && ! StoragePlan::whereKey($newPlanId)->exists()) {
            $this->notifyFailure('Plan de almacenamiento no válido.');
            return;
        }

        $user->update(['storage_plan_id' => $newPlanId]);
        $name = $newPlanId ? optional(StoragePlan::find($newPlanId))->name : 'Sin plan';
        $this->notifySuccess("Plan de almacenamiento de «{$user->name}» actualizado a «{$name}».");
        $this->dispatch('pg:eventRefresh-' . $this->tableName);
    }

    public function changeAiPlan(int $userId, string $planId): void
    {
        $user = User::find($userId);

        if (! $user) {
            $this->notifyFailure('Usuario no encontrado.');
            return;
        }

        $newPlanId = $planId !== '' ? (int) $planId : null;

        if ($newPlanId !== null && ! AiPlan::whereKey($newPlanId)->exists()) {
            $this->notifyFailure('Plan de IA no válido.');
            return;
        }

        $user->update(['ai_plan_id' => $newPlanId]);
        $name = $newPlanId ? optional(AiPlan::find($newPlanId))->name : 'Sin plan IA';
        $this->notifySuccess("Plan de IA de «{$user->name}» actualizado a «{$name}».");
        $this->dispatch('pg:eventRefresh-' . $this->tableName);
    }

    private function storageUsageBar(User $user): string
    {
        $used = User::formatBytes($user->storage_used_bytes ?? 0);

        if (! $user->storage_plan_id || ! $user->storagePlan) {
            return '<div class="text-xs text-zinc-500 dark:text-zinc-400">'
                . e($used) . ' / <span class="text-zinc-400">' . e(__('admin_ui.users.storage_unlimited')) . '</span></div>';
        }

        $pct   = $user->storagePct();
        $limit = $user->storagePlan->limitLabel();
        $color = $pct >= 90 ? 'bg-rose-500' : ($pct >= 70 ? 'bg-amber-500' : 'bg-violet-600');

        return '<div class="min-w-[140px]">'
            . '<div class="flex items-center justify-between text-xs text-zinc-600 dark:text-zinc-300 mb-1">'
            . '<span>' . e($used) . ' / ' . e($limit) . '</span>'
            . '<span class="text-zinc-400">' . $pct . '%</span>'
            . '</div>'
            . '<span class="block w-full h-1.5 rounded-full bg-zinc-200 dark:bg-zinc-700 overflow-hidden">'
            . '<span class="block h-full rounded-full ' . $color . '" style="width:' . $pct . '%"></span>'
            . '</span>'
            . '</div>';
    }

    public function columns(): array
    {
        return [
            Column::make(__('admin_ui.users.columns.id'), 'id')->sortable(),
            Column::make(__('admin_ui.users.columns.name'), 'name')->sortable()->searchable(),
            Column::make(__('admin_ui.users.columns.email'), 'email')->sortable()->searchable(),
            Column::make(__('admin_ui.users.columns.roles'), 'roles_list'),
            Column::make(__('admin_ui.users.columns.verification'), 'email_verified_label'),
            Column::make(__('admin_ui.users.columns.storage_plan'), 'storage_plan_label'),
            Column::make('Plan IA', 'ai_plan_label'),
            Column::make(__('admin_ui.users.columns.storage_usage'), 'storage_usage_label'),
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

    public function clearSelection(): void
    {
        $this->checkboxAll = false;
        $this->checkboxValues = [];
        $this->dispatch('pgBulkActions::clear', $this->tableName);
    }

    public function exportSelected(): BinaryFileResponse|null
    {
        $selectedIds = $this->selectedUserIds();

        if ($selectedIds === []) {
            $this->notifyWarning(__('admin_ui.users.notify.no_selection'));
            return null;
        }

        $this->clearSelection();

        return Excel::download(
            new AdminUsersExport($selectedIds),
            'admin-users-selected-' . now()->format('Ymd-His') . '.xlsx'
        );
    }

    public function deleteSelected(): void
    {
        $selectedIds = $this->selectedUserIds();

        if ($selectedIds === []) {
            $this->notifyWarning(__('admin_ui.users.notify.no_selection'));
            return;
        }

        $users = User::query()
            ->whereIn('id', $selectedIds)
            ->get();

        if ($users->isEmpty()) {
            $this->clearSelection();
            $this->notifyWarning(__('admin_ui.users.notify.no_selection'));
            return;
        }

        $deletedCount = 0;

        foreach ($users as $user) {
            $user->delete();
            $deletedCount++;
        }

        $this->clearSelection();
        $this->notifyWarning(__('admin_ui.users.notify.bulk_deleted', ['count' => $deletedCount]));
        $this->dispatch('pg:eventRefresh-' . $this->tableName);
    }

    #[On('deleteUser')]
    public function deleteUser(int $id): void
    {
        $user = User::find($id);

        if (! $user) {
            $this->notifyCrud('users', 'not_found');
            return;
        }

        $user->delete();
        $this->notifyCrud('users', 'deleted');
        $this->dispatch('pg:eventRefresh-' . $this->tableName);
    }

    private function selectedUserIds(): array
    {
        return collect($this->checkboxValues)
            ->map(static fn (string|int $id): int => (int) $id)
            ->filter(static fn (int $id): bool => $id > 0)
            ->unique()
            ->values()
            ->all();
    }
}
