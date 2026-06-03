<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\DispatchesStarchoNotify;
use App\Models\StoragePlan;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\On;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class StorageUsersTable extends PowerGridComponent
{
    use DispatchesStarchoNotify;

    public string $tableName = 'storage-users-table';

    public string $sortField = 'storage_used_bytes';
    public string $sortDirection = 'desc';

    /** @var \Illuminate\Support\Collection<int,\App\Models\StoragePlan>|null Memoized to avoid a query per row. */
    private ?\Illuminate\Support\Collection $plansCache = null;

    private function plans(): \Illuminate\Support\Collection
    {
        return $this->plansCache ??= StoragePlan::orderBy('sort_order')->get();
    }

    public function setUp(): array
    {
        return [
            PowerGrid::header()->showSearchInput(),
            PowerGrid::footer()->showPerPage(15)->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        return User::query()->with('storagePlan');
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('name')
            ->add('email')
            ->add('storage_used_label', fn (User $user) => User::formatBytes($user->storage_used_bytes ?? 0))
            ->add('storage_usage_bar', fn (User $user) => $this->storageUsageBar($user))
            ->add('plan_select', fn (User $user) => $this->planSelect($user));
    }

    private function storageUsageBar(User $user): string
    {
        $used = User::formatBytes($user->storage_used_bytes ?? 0);

        if (! $user->storage_plan_id || ! $user->storagePlan) {
            return '<div class="text-xs text-zinc-500 dark:text-zinc-400">'
                . e($used) . ' / <span class="text-zinc-400">Ilimitado</span></div>';
        }

        $pct   = $user->storagePct();
        $limit = $user->storagePlan->limitLabel();
        $color = $pct >= 90 ? 'bg-rose-500' : ($pct >= 70 ? 'bg-amber-500' : 'bg-violet-600');

        return '<div class="min-w-[150px]">'
            . '<div class="flex items-center justify-between text-xs text-zinc-600 dark:text-zinc-300 mb-1">'
            . '<span>' . e($used) . ' / ' . e($limit) . '</span>'
            . '<span class="text-zinc-400">' . $pct . '%</span>'
            . '</div>'
            . '<span class="block w-full h-1.5 rounded-full bg-zinc-200 dark:bg-zinc-700 overflow-hidden">'
            . '<span class="block h-full rounded-full ' . $color . '" style="width:' . $pct . '%"></span>'
            . '</span>'
            . '</div>';
    }

    private function planSelect(User $user): string
    {
        $plans = $this->plans();
        $current = (int) ($user->storage_plan_id ?? 0);

        $options = '<option value="">Sin plan</option>';
        foreach ($plans as $plan) {
            $selected = $current === (int) $plan->id ? ' selected' : '';
            $label = e($plan->name) . ' · ' . e($plan->limitLabel());
            $options .= '<option value="' . $plan->id . '"' . $selected . '>' . $label . '</option>';
        }

        return '<select wire:change="changePlan(' . $user->id . ', $event.target.value)" '
            . 'class="h-8 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 '
            . 'text-xs text-zinc-700 dark:text-zinc-300 px-2 pr-7 focus:outline-none focus:ring-2 focus:ring-violet-400/20 focus:border-violet-400 transition">'
            . $options
            . '</select>';
    }

    public function changePlan(int $userId, string $planId): void
    {
        $user = User::find($userId);

        if (! $user) {
            $this->notifyFailure('Usuario no encontrado.');
            return;
        }

        $newPlanId = $planId !== '' ? (int) $planId : null;

        if ($newPlanId !== null && ! StoragePlan::whereKey($newPlanId)->exists()) {
            $this->notifyFailure('Plan no válido.');
            return;
        }

        $user->storage_plan_id = $newPlanId;
        $user->save();

        $planName = $newPlanId ? optional(StoragePlan::find($newPlanId))->name : 'Sin plan';
        $this->notifySuccess("Plan de «{$user->name}» actualizado a «{$planName}».");
        $this->dispatch('pg:eventRefresh-' . $this->tableName);
    }

    public function columns(): array
    {
        return [
            Column::make('ID', 'id')->sortable(),
            Column::make('Nombre', 'name')->sortable()->searchable(),
            Column::make('Email', 'email')->sortable()->searchable(),
            Column::make('Usado', 'storage_used_label', 'storage_used_bytes')->sortable(),
            Column::make('Almacenamiento', 'storage_usage_bar'),
            Column::make('Cambiar plan', 'plan_select'),
        ];
    }

    public function filters(): array
    {
        return [];
    }
}
