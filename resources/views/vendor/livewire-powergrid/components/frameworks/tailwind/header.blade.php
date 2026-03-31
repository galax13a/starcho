<div class="px-4 pt-4 pb-3 border-b border-zinc-100 dark:border-zinc-700/60">

    {{-- Controls left + search right (single line on desktop) --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">

        {{-- Left: actions / export / toggle-columns --}}
        <div class="flex flex-wrap items-center gap-2">
            <div x-data="pgRenderActions">
                <span class="pg-actions" x-html="toHtml"></span>
            </div>

            @includeIf(data_get($setUp, 'header.includeViewOnTop'))

            @if (data_get($setUp, 'exportable'))
                <div id="pg-header-export">
                    @include(data_get($theme, 'root') . '.header.export')
                </div>
            @endif

            @includeIf(data_get($theme, 'root') . '.header.toggle-columns')
            @includeIf(data_get($theme, 'root') . '.header.soft-deletes')

            @if (config('livewire-powergrid.filter') == 'outside' && count($this->filters()) > 0)
                @includeIf(data_get($theme, 'root') . '.header.filters')
            @endif

            @includeWhen(boolval(data_get($setUp, 'header.wireLoading')),
                data_get($theme, 'root') . '.header.loading')
        </div>

        {{-- Right: search input --}}
        @if (data_get($setUp, 'header.searchInput'))
            <div class="relative w-full md:w-80 group">
                <input
                    wire:model.live.debounce.500ms="search"
                    type="text"
                    placeholder="{{ trans('livewire-powergrid::datatable.placeholders.search') }}"
                    class="
                        w-full rounded-lg border border-zinc-200 dark:border-zinc-600
                        bg-white dark:bg-zinc-700/50
                        text-sm text-zinc-700 dark:text-zinc-200
                        placeholder-zinc-400 dark:placeholder-zinc-500
                        py-2 pl-4 pr-9
                        outline-none
                        transition-all duration-200
                        focus:border-cyan-400 dark:focus:border-cyan-500
                        focus:ring-2 focus:ring-cyan-400/20 dark:focus:ring-cyan-500/20
                        hover:border-zinc-300 dark:hover:border-zinc-500
                    "
                >

                {{-- Search icon (empty) / Clear button (typing) --}}
                @if ($search)
                    <a wire:click.prevent="$set('search','')"
                       class="absolute right-2.5 top-1/2 -translate-y-1/2 p-0.5 rounded-md text-zinc-400 hover:text-[#ff00c8] dark:text-zinc-500 dark:hover:text-[#ff00c8] cursor-pointer transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <path d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </a>
                @else
                    <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-zinc-400 dark:text-zinc-500 group-focus-within:text-cyan-500 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </span>
                @endif
            </div>
        @endif
    </div>

    @includeIf(data_get($theme, 'root') . '.header.enabled-filters')
    @includeWhen(data_get($setUp, 'exportable.batchExport.queues', 0), data_get($theme, 'root') . '.header.batch-exporting')
    @includeWhen($multiSort, data_get($theme, 'root') . '.header.multi-sort')
    @includeIf(data_get($setUp, 'header.includeViewOnBottom'))
    @includeIf(data_get($theme, 'root') . '.header.message-soft-deletes')
</div>
