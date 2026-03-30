<div
    class="flex items-center justify-between gap-4 px-4 py-3"
    wire:loading.class="opacity-60 pointer-events-none"
    wire:target="loadMore"
>
    {{-- Record count --}}
    @if($paginator->count() > 0)
        <p class="text-xs text-zinc-500 dark:text-zinc-400 shrink-0">
            @if ($recordCount === 'full')
                {{ trans('livewire-powergrid::datatable.pagination.showing') }}
                <span class="font-semibold text-zinc-700 dark:text-zinc-300">{{ $paginator->firstItem() }}</span>
                {{ trans('livewire-powergrid::datatable.pagination.to') }}
                <span class="font-semibold text-zinc-700 dark:text-zinc-300">{{ $paginator->lastItem() }}</span>
                {{ trans('livewire-powergrid::datatable.pagination.of') }}
                <span class="font-semibold text-zinc-700 dark:text-zinc-300">{{ $paginator->total() }}</span>
                {{ trans('livewire-powergrid::datatable.pagination.results') }}
            @elseif($recordCount === 'short')
                <span class="font-semibold text-zinc-700 dark:text-zinc-300">{{ $paginator->firstItem() }}–{{ $paginator->lastItem() }}</span>
                / <span class="font-semibold text-zinc-700 dark:text-zinc-300">{{ $paginator->total() }}</span>
            @elseif($recordCount === 'min')
                <span class="font-semibold text-zinc-700 dark:text-zinc-300">{{ $paginator->firstItem() }}–{{ $paginator->lastItem() }}</span>
            @endif
        </p>
    @else
        <p class="text-xs text-zinc-400 dark:text-zinc-500">
            {{ trans('livewire-powergrid::datatable.pagination.showing') }} 0
        </p>
    @endif

    {{-- Page buttons --}}
    @if ($paginator->hasPages())
        <nav class="flex items-center gap-1" role="navigation" aria-label="Pagination">

            {{-- First --}}
            @if (!$paginator->onFirstPage())
                <button
                    wire:click="gotoPage(1, '{{ $paginator->getPageName() }}')"
                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-zinc-200 dark:border-zinc-600 bg-white dark:bg-zinc-700/50 text-zinc-500 dark:text-zinc-400 hover:border-zinc-300 dark:hover:border-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-200 transition-colors text-xs"
                    title="{{ __('admin_ui.powergrid.first_page') }}"
                >
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m18.75 4.5-7.5 7.5 7.5 7.5m-6-15L5.25 12l7.5 7.5" />
                    </svg>
                </button>

                {{-- Prev --}}
                <button
                    wire:click="previousPage('{{ $paginator->getPageName() }}')"
                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-zinc-200 dark:border-zinc-600 bg-white dark:bg-zinc-700/50 text-zinc-500 dark:text-zinc-400 hover:border-zinc-300 dark:hover:border-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-200 transition-colors"
                    rel="prev"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                    </svg>
                </button>
            @else
                <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-zinc-100 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 text-zinc-300 dark:text-zinc-600 cursor-not-allowed">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m18.75 4.5-7.5 7.5 7.5 7.5m-6-15L5.25 12l7.5 7.5" />
                    </svg>
                </span>
                <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-zinc-100 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 text-zinc-300 dark:text-zinc-600 cursor-not-allowed">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                    </svg>
                </span>
            @endif

            {{-- Page numbers --}}
            @foreach ($elements as $element)
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-violet-400/60 dark:border-violet-500/50 bg-violet-50 dark:bg-violet-900/30 text-violet-700 dark:text-violet-300 text-xs font-semibold select-none">
                                {{ $page }}
                            </span>
                        @elseif (abs($page - $paginator->currentPage()) <= 2)
                            <button
                                wire:click="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')"
                                class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-zinc-200 dark:border-zinc-600 bg-white dark:bg-zinc-700/50 text-zinc-600 dark:text-zinc-400 text-xs hover:border-zinc-300 dark:hover:border-zinc-500 hover:text-zinc-800 dark:hover:text-zinc-200 transition-colors"
                            >{{ $page }}</button>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next --}}
            @if ($paginator->hasMorePages())
                <button
                    wire:click="nextPage('{{ $paginator->getPageName() }}')"
                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-zinc-200 dark:border-zinc-600 bg-white dark:bg-zinc-700/50 text-zinc-500 dark:text-zinc-400 hover:border-zinc-300 dark:hover:border-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-200 transition-colors"
                    rel="next"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                    </svg>
                </button>

                {{-- Last --}}
                <button
                    wire:click="gotoPage({{ $paginator->lastPage() }}, '{{ $paginator->getPageName() }}')"
                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-zinc-200 dark:border-zinc-600 bg-white dark:bg-zinc-700/50 text-zinc-500 dark:text-zinc-400 hover:border-zinc-300 dark:hover:border-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-200 transition-colors text-xs"
                    title="{{ __('admin_ui.powergrid.last_page') }}"
                >
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m5.25 4.5 7.5 7.5-7.5 7.5m6-15 7.5 7.5-7.5 7.5" />
                    </svg>
                </button>
            @else
                <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-zinc-100 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 text-zinc-300 dark:text-zinc-600 cursor-not-allowed">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                    </svg>
                </span>
                <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-zinc-100 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 text-zinc-300 dark:text-zinc-600 cursor-not-allowed">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m5.25 4.5 7.5 7.5-7.5 7.5m6-15 7.5 7.5-7.5 7.5" />
                    </svg>
                </span>
            @endif

        </nav>
    @endif
</div>
