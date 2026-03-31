<div
    x-data="{ open: false, countChecked: @entangle('checkboxValues').live }"
    class="relative"
    x-on:keydown.esc="open = false"
    x-on:click.outside="open = false"
>
    <button
        @click.prevent="open = !open"
        title="{{ __('admin_ui.powergrid.export_data') }}"
        aria-label="{{ __('admin_ui.powergrid.export_data') }}"
        class="
            inline-flex items-center justify-center size-8 rounded-lg text-xs font-medium
            border border-zinc-200 dark:border-zinc-600
            bg-white dark:bg-zinc-700/50
            text-zinc-500 dark:text-zinc-400
            hover:text-[#22c55e] hover:border-[#22c55e]/50 hover:bg-[#22c55e]/5
            dark:hover:text-[#22c55e] dark:hover:border-[#22c55e]/40
            transition-all duration-200
            focus:outline-none focus:ring-2 focus:ring-[#22c55e]/25
        "
    >
        <x-livewire-powergrid::icons.download class="w-4 h-4" />
    </button>

    <div
        x-cloak
        x-show="open"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="
            absolute right-0 z-20 mt-2 w-52
            rounded-xl border border-zinc-200 dark:border-zinc-700
            bg-white dark:bg-zinc-800
            shadow-lg shadow-zinc-200/50 dark:shadow-zinc-900/50
        "
        tabindex="-1"
        @keydown.tab="open = false"
        @keydown.escape="open = false"
    >
        <div class="p-1">
            @if (in_array('xlsx', data_get($setUp, 'exportable.type')))
                <div class="px-2 py-1.5 text-xs font-semibold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">
                    XLSX
                </div>
                <button
                    wire:click.prevent="exportToXLS"
                    x-on:click="open = false"
                    class="
                        flex items-center gap-2 w-full px-3 py-2 rounded-lg text-sm
                        text-zinc-700 dark:text-zinc-300
                        hover:bg-[#ff00c8]/5 hover:text-[#ff00c8] dark:hover:bg-[#ff00c8]/10 dark:hover:text-[#ff00c8]
                        transition-colors duration-150
                    "
                >
                    <span class="text-xs text-zinc-400">({{ $this->total }})</span>
                    {{ count($enabledFilters) === 0 ? __('livewire-powergrid::datatable.labels.all') : __('livewire-powergrid::datatable.labels.filtered') }}
                </button>
            @endif

            @if (in_array('csv', data_get($setUp, 'exportable.type')))
                <div class="px-2 py-1.5 text-xs font-semibold uppercase tracking-wider text-zinc-400 dark:text-zinc-500 mt-1">
                    CSV
                </div>
                <button
                    wire:click.prevent="exportToCsv"
                    x-on:click="open = false"
                    class="
                        flex items-center gap-2 w-full px-3 py-2 rounded-lg text-sm
                        text-zinc-700 dark:text-zinc-300
                        hover:bg-[#ff00c8]/5 hover:text-[#ff00c8] dark:hover:bg-[#ff00c8]/10 dark:hover:text-[#ff00c8]
                        transition-colors duration-150
                    "
                >
                    <span class="text-xs text-zinc-400">({{ $this->total }})</span>
                    {{ count($enabledFilters) === 0 ? __('livewire-powergrid::datatable.labels.all') : __('livewire-powergrid::datatable.labels.filtered') }}
                </button>
            @endif
        </div>
    </div>
</div>
