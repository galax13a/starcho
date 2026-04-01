<div
    x-data="{ selected: @entangle('checkboxValues').live }"
    class="flex flex-col gap-2 w-full"
>
    {{-- Fila 1: acciones principales --}}
    <div class="flex flex-wrap items-center justify-end gap-2">
        <button
            onclick="Livewire.dispatch('openAdminContact', {id: 0})"
            class="inline-flex items-center gap-1.5 h-8 px-3 bg-violet-600 hover:bg-violet-700 text-white text-xs font-medium rounded-lg transition">
            <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
            {{ __('admin_ui.contacts.new') }}
        </button>

        <x-starcho-btn-excel
            action="export"
            module="contacts"
            section="admin"
            bulkWireMethod="exportSelected"
            :requireSelection="true"
            :requireSelectionMessage="__('admin_ui.common.select_item_to_export')"
        />
        <x-starcho-btn-excel action="import" module="contacts" section="admin" />

        {{-- Filtro estado --}}
        <select wire:model.live="filterStatus"
            class="h-8 rounded-lg border border-zinc-200 dark:border-zinc-600 bg-white dark:bg-zinc-700/50 text-xs text-zinc-700 dark:text-zinc-200 px-3 focus:outline-none focus:ring-2 focus:ring-violet-400/30 transition-colors cursor-pointer">
            <option value="">{{ __('admin_ui.contacts.filters.all_statuses') }}</option>
            <option value="lead">{{ __('admin_ui.contacts.status.lead') }}</option>
            <option value="prospect">{{ __('admin_ui.contacts.status.prospect') }}</option>
            <option value="customer">{{ __('admin_ui.contacts.status.customer') }}</option>
            <option value="churned">{{ __('admin_ui.contacts.status.churned') }}</option>
        </select>
    </div>

    {{-- Fila 2: bulk toolbar (solo cuando hay seleccion) --}}
    <div
        x-cloak
        x-show="selected.length > 0"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 -translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-1"
        class="flex flex-wrap items-center gap-2 w-full rounded-xl border border-violet-200 dark:border-violet-700/40 bg-violet-50 dark:bg-violet-900/20 px-3 py-1.5"
    >
        <span class="inline-flex items-center h-8 px-3 rounded-lg bg-white/80 dark:bg-zinc-900/70 text-xs font-semibold text-violet-700 dark:text-violet-300">
            <span x-text="selected.length"></span>
            <span class="ml-1">{{ __('admin_ui.contacts.bulk.selected') }}</span>
        </span>

        <button
            type="button"
            @click="window.Starcho.confirm({
                title: @js(__('js.delete.title')),
                message: @js(__('admin_ui.contacts.bulk.delete_confirm')),
                okText: @js(__('js.delete.ok')),
                cancelText: @js(__('js.confirm.cancel')),
                onConfirm: () => $wire.deleteSelected(),
            })"
            class="inline-flex items-center gap-1.5 h-8 px-3 rounded-lg bg-rose-600 hover:bg-rose-700 text-violet-700 dark:text-violet-300 text-xs font-medium transition"
        >
            <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0V5.25A2.25 2.25 0 0 0 13.5 3h-3a2.25 2.25 0 0 0-2.25 2.25v.143"/>
            </svg>
            {{ __('admin_ui.contacts.bulk.delete_selected') }}
        </button>

        <button
            type="button"
            wire:click="clearSelection"
            class="inline-flex items-center gap-1.5 h-8 px-3 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 text-xs font-medium text-zinc-700 dark:text-zinc-200 transition hover:bg-zinc-50 dark:hover:bg-zinc-800"
        >
            <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            {{ __('admin_ui.contacts.bulk.clear_selection') }}
        </button>
    </div>
</div>
