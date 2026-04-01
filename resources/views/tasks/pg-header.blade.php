<div
    x-data="{ selected: @entangle('checkboxValues').live }"
    class="flex flex-col gap-2 w-full"
>
    <div class="flex w-full items-center justify-end gap-2 overflow-x-auto md:flex-nowrap">
        {{-- Filtro estado — Kick input --}}
        <select wire:model.live="filterStatus" class="sc-select sc-select-kick starchi-kick-filter"
                style="height:36px;font-size:12.5px;padding:0 34px 0 12px;width:auto;">
            <option value="">{{ __('tasks.filter_all_statuses') }}</option>
            <option value="pending">⬜ {{ __('tasks.status_pending') }}</option>
            <option value="in_progress">🔵 {{ __('tasks.status_in_progress') }}</option>
            <option value="completed">✅ {{ __('tasks.status_completed') }}</option>
            <option value="cancelled">❌ {{ __('tasks.status_cancelled') }}</option>
        </select>

        {{-- Filtro prioridad — Kick input --}}
        <select wire:model.live="filterPriority" class="sc-select sc-select-kick starchi-kick-filter"
                style="height:36px;font-size:12.5px;padding:0 34px 0 12px;width:auto;">
            <option value="">{{ __('tasks.filter_all_priorities') }}</option>
            <option value="low">🟢 {{ __('tasks.priority_low') }}</option>
            <option value="medium">🟡 {{ __('tasks.priority_medium') }}</option>
            <option value="high">🟠 {{ __('tasks.priority_high') }}</option>
            <option value="urgent">🔴 {{ __('tasks.priority_urgent') }}</option>
        </select>

        <x-starcho-btn-excel
            action="export"
            module="tasks"
            section="app"
            bulkWireMethod="exportSelected"
            :requireSelection="true"
            :requireSelectionMessage="__('tasks.notify.no_selection')"
        />
        <x-starcho-btn-excel action="import" module="tasks" section="app" />
    </div>

    <div
        x-cloak
        x-show="selected.length > 0"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 -translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-1"
        class="inline-flex items-center gap-2 self-end rounded-xl border border-violet-200 dark:border-violet-700/40 bg-violet-50 dark:bg-violet-900/20 px-3 py-1.5"
    >
        <span class="inline-flex items-center h-8 px-3 rounded-lg bg-white/80 dark:bg-zinc-900/70 text-xs font-semibold text-violet-700 dark:text-violet-300">
            <span x-text="selected.length"></span>
            <span class="ml-1">{{ __('tasks.bulk_selected') }}</span>
        </span>

        <button
            type="button"
            @click="window.Starcho.confirm({
                title: @js(__('js.delete.title')),
                message: @js(__('tasks.bulk_delete_confirm')),
                okText: @js(__('js.delete.ok')),
                cancelText: @js(__('js.confirm.cancel')),
                onConfirm: () => $wire.deleteSelected(),
            })"
            class="inline-flex items-center gap-1.5 h-8 px-3 rounded-lg bg-rose-600 hover:bg-rose-700 text-violet-700 dark:text-violet-300 text-xs font-medium transition"
        >
            <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0V5.25A2.25 2.25 0 0 0 13.5 3h-3a2.25 2.25 0 0 0-2.25 2.25v.143"/>
            </svg>
            {{ __('tasks.bulk_delete_selected') }}
        </button>

        <button
            type="button"
            wire:click="clearSelection"
            class="inline-flex items-center gap-1.5 h-8 px-3 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 text-xs font-medium text-zinc-700 dark:text-zinc-200 transition hover:bg-zinc-50 dark:hover:bg-zinc-800"
        >
            <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            {{ __('tasks.bulk_clear_selection') }}
        </button>
    </div>
</div>
