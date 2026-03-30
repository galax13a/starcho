@if (data_get($setUp, 'header.toggleColumns'))
    <div x-data="{ open: false }" class="relative" @click.outside="open = false">

        <button
            data-cy="toggle-columns-{{ $tableName }}"
            @click.prevent="open = !open"
            title="{{ __('admin_ui.powergrid.toggle_columns') }}"
            class="
                inline-flex items-center gap-1.5 px-3 h-8 rounded-lg text-xs font-medium
                border border-zinc-200 dark:border-zinc-600
                bg-white dark:bg-zinc-700/50
                text-zinc-500 dark:text-zinc-400
                hover:text-[#00f2ff] hover:border-[#00f2ff]/50 hover:bg-[#00f2ff]/5
                dark:hover:text-[#00f2ff] dark:hover:border-[#00f2ff]/40
                transition-all duration-200
                focus:outline-none focus:ring-2 focus:ring-[#00f2ff]/25
            "
        >
            <x-livewire-powergrid::icons.eye-off class="w-4 h-4" />
            {{ __('admin_ui.powergrid.columns') }}
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
                absolute left-0 z-20 mt-2 w-52
                rounded-xl border border-zinc-200 dark:border-zinc-700
                bg-white dark:bg-zinc-800
                shadow-lg shadow-zinc-200/50 dark:shadow-zinc-900/50
                focus:outline-none
            "
            tabindex="-1"
            @keydown.tab="open = false"
            @keydown.escape="open = false"
        >
            <div class="p-1">
                @foreach ($this->visibleColumns as $column)
                    <div
                        wire:key="toggle-column-{{ data_get($column, 'isAction') ? 'actions' : data_get($column, 'field') }}"
                        data-cy="toggle-field-{{ data_get($column, 'isAction') ? 'actions' : data_get($column, 'field') }}"
                        wire:click="$dispatch('pg:toggleColumn-{{ $tableName }}', { field: '{{ data_get($column, 'field') }}'})"
                        @class([
                            'flex items-center justify-between gap-2 px-3 py-2 rounded-lg text-sm cursor-pointer transition-colors duration-150',
                            'text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700/50' => !data_get($column, 'hidden'),
                            'text-zinc-400 dark:text-zinc-500 bg-zinc-50/50 dark:bg-zinc-700/30 hover:bg-zinc-100 dark:hover:bg-zinc-700' => data_get($column, 'hidden'),
                        ])
                    >
                        <span class="font-medium">{!! data_get($column, 'title') !!}</span>
                        @if (!data_get($column, 'hidden'))
                            <x-livewire-powergrid::icons.eye class="h-4 w-4 text-[#00f2ff] shrink-0" />
                        @else
                            <x-livewire-powergrid::icons.eye-off class="h-4 w-4 text-zinc-400 shrink-0" />
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endif
