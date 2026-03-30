<div>
    @includeIf(data_get($setUp, 'footer.includeViewOnTop'))
    <footer
        id="pg-footer"
        class="{{ theme_style($theme, 'footer.footer') }}"
    >
        {{-- Per-page select --}}
        @if (filled(data_get($setUp, 'footer.perPage')) &&
                count(data_get($setUp, 'footer.perPageValues')) > 1 &&
                blank(data_get($setUp, 'footer.pagination')))
            <div class="flex items-center gap-2 px-4 py-3 shrink-0">
                <span class="text-xs text-zinc-400 dark:text-zinc-500 whitespace-nowrap">{{ trans('livewire-powergrid::datatable.labels.results_per_page') }}</span>
                <div class="relative">
                    <select
                        wire:model.live="setUp.footer.perPage"
                        class="{{ theme_style($theme, 'footer.select') }}"
                    >
                        @foreach (data_get($setUp, 'footer.perPageValues') as $value)
                            <option value="{{ $value }}">
                                @if ($value == 0)
                                    {{ trans('livewire-powergrid::datatable.labels.all') }}
                                @else
                                    {{ $value }}
                                @endif
                            </option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-1.5 flex items-center text-zinc-400 dark:text-zinc-500">
                        <x-livewire-powergrid::icons.down class="w-3 h-3" />
                    </div>
                </div>
            </div>
        @endif

        {{-- Pagination --}}
        <div class="flex-1">
            @if (method_exists($this->records, 'links'))
                {!! $this->records->links(data_get($setUp, 'footer.pagination') ?: data_get($theme, 'root') . '.pagination', [
                    'recordCount' => data_get($setUp, 'footer.recordCount'),
                    'perPage' => data_get($setUp, 'footer.perPage'),
                    'perPageValues' => data_get($setUp, 'footer.perPageValues'),
                ]) !!}
            @endif
        </div>
    </footer>
    @includeIf(data_get($setUp, 'footer.includeViewOnBottom'))
</div>
