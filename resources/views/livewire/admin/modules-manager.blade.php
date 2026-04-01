<div
    x-data="{}"
    @notify.window="
        const el = document.createElement('div');
        el.className = 'fixed bottom-5 right-5 z-50 px-4 py-3 rounded-xl text-sm font-medium shadow-lg border transition ' +
            ($event.detail.type === 'success' ? 'bg-emerald-50 text-emerald-800 border-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-300 dark:border-emerald-700' : 'bg-amber-50 text-amber-800 border-amber-200 dark:bg-amber-900/30 dark:text-amber-300 dark:border-amber-700');
        el.textContent = $event.detail.message;
        document.body.appendChild(el);
        setTimeout(() => el.remove(), 4000);
    ">

        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2">
                <button
                    type="button"
                    wire:click="exportExcel"
                    class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-semibold bg-amber-500 hover:bg-amber-600 text-white rounded-lg transition"
                >
                    <i class="fas fa-file-excel text-xs"></i>
                    {{ __('admin_ui.modules.actions.export_excel') }}
                </button>

                <button
                    type="button"
                    wire:click="openImportExcelModal"
                    class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-semibold bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition"
                >
                    <i class="fas fa-file-import text-xs"></i>
                    {{ __('admin_ui.modules.actions.import_excel') }}
                </button>

                <button
                    type="button"
                    @click="window.Starcho.confirm({
                        title: @js(__('js.delete.title')),
                        message: @js(__('admin_ui.modules.clear_confirm')),
                        okText: @js(__('js.delete.ok')),
                        cancelText: @js(__('js.confirm.cancel')),
                        onConfirm: () => $wire.clearAll(),
                    })"
                    class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-semibold bg-rose-600 hover:bg-rose-700 text-white rounded-lg transition"
                >
                    <i class="fas fa-trash-alt text-xs"></i>
                    {{ __('admin_ui.modules.actions.clear_all') }}
                </button>
            </div>

            <div class="mb-0">
                <label for="modules-search" class="sr-only">Buscar modulos</label>
                <input
                    id="modules-search"
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Buscar modulo por nombre..."
                    class="w-full md:w-96 rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 text-zinc-800 dark:text-zinc-100 px-4 py-2 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-violet-500/40"
                >
            </div>
        </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach($modules as $module)
        <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-5 flex flex-col gap-4 shadow-sm">

            {{-- Header --}}
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0
                    {{ $module['active'] ? 'bg-violet-100 dark:bg-violet-900/30' : 'bg-zinc-100 dark:bg-zinc-800' }}">
                    @php
                        $iconSvgs = [
                            'clipboard-document-list' => '<svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z"/></svg>',
                            'user-group' => '<svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z"/></svg>',
                            'document-text' => '<svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5A3.375 3.375 0 0 0 10.125 2.25H6.75A2.25 2.25 0 0 0 4.5 4.5v15A2.25 2.25 0 0 0 6.75 21.75h10.5a2.25 2.25 0 0 0 2.25-2.25v-5.25Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 10.5h7.5m-7.5 3h7.5m-7.5 3h4.5"/></svg>',
                            'globe' => '<svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Zm0 0c2.5-2.5 2.5-15.5 0-18m0 18c-2.5-2.5-2.5-15.5 0-18M3 12h18"/></svg>',
                            'default'    => '<svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25"/></svg>',
                        ];
                        $iconKey = $module['icon'] ?? 'default';
                        $svg = $iconSvgs[$iconKey] ?? $iconSvgs['default'];
                    @endphp
                    <span class="{{ $module['active'] ? 'text-violet-600 dark:text-violet-400' : 'text-zinc-400 dark:text-zinc-500' }}">
                        {!! $svg !!}
                    </span>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        <h3 class="font-semibold text-zinc-900 dark:text-zinc-100 text-sm">{{ $module['name'] }}</h3>
                        @if($module['installed'] && $module['active'])
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">{{ __('admin_ui.modules.status.active') }}</span>
                        @elseif($module['installed'])
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">{{ __('admin_ui.modules.status.installed') }}</span>
                        @else
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400">{{ __('admin_ui.modules.status.not_installed') }}</span>
                        @endif
                    </div>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">{{ $module['description'] }}</p>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex gap-2 border-t border-zinc-100 dark:border-zinc-800 pt-3">
                @if(!$module['installed'])
                    <button wire:click="install({{ $module['id'] }})" wire:loading.attr="disabled"
                            class="flex-1 text-xs font-semibold py-1.5 px-3 rounded-lg bg-violet-600 hover:bg-violet-700 text-white transition">
                        <span wire:loading.remove wire:target="install({{ $module['id'] }})">{{ __('admin_ui.modules.actions.install') }}</span>
                        <span wire:loading wire:target="install({{ $module['id'] }})">{{ __('admin_ui.modules.actions.installing') }}</span>
                    </button>
                    <a href="{{ route('admin.modules.config', $module['id']) }}"
                       class="text-xs font-semibold py-1.5 px-3 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-700 dark:bg-blue-900/20 dark:hover:bg-blue-900/40 dark:text-blue-400 border border-blue-200 dark:border-blue-700 transition">
                        {{ __('admin_ui.modules.actions.config') }}
                    </a>
                @else
                    @if($module['active'])
                        <button wire:click="deactivate({{ $module['id'] }})"
                                class="flex-1 text-xs font-semibold py-1.5 px-3 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-700 dark:bg-amber-900/20 dark:hover:bg-amber-900/40 dark:text-amber-400 border border-amber-200 dark:border-amber-700 transition">
                            {{ __('admin_ui.modules.actions.deactivate') }}
                        </button>
                    @else
                        <button wire:click="activate({{ $module['id'] }})"
                                class="flex-1 text-xs font-semibold py-1.5 px-3 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 dark:bg-emerald-900/20 dark:hover:bg-emerald-900/40 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-700 transition">
                            {{ __('admin_ui.modules.actions.activate') }}
                        </button>
                    @endif
                    <a href="{{ route('admin.modules.config', $module['id']) }}"
                       class="text-xs font-semibold py-1.5 px-3 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-700 dark:bg-blue-900/20 dark:hover:bg-blue-900/40 dark:text-blue-400 border border-blue-200 dark:border-blue-700 transition">
                        {{ __('admin_ui.modules.actions.config') }}
                    </a>
                    <button wire:click="uninstall({{ $module['id'] }})"
                            class="text-xs font-semibold py-1.5 px-3 rounded-lg bg-red-50 hover:bg-red-100 text-red-600 dark:bg-red-900/20 dark:hover:bg-red-900/40 dark:text-red-400 border border-red-200 dark:border-red-700 transition">
                        {{ __('admin_ui.modules.actions.uninstall') }}
                    </button>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    <x-starcho-popup-admin-import
        modal-name="modal-admin-modules-import-excel"
        submit-method="importExcel"
        loading-target="importExcel"
        title="{{ __('admin_ui.modules.actions.import_excel') }}"
        file-model="importExcelFile"
    />
</div>
