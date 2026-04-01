<div x-data data-menu-tree-root>

    {{-- Panel tabs --}}
    <div class="flex items-center gap-1 mb-5 p-1 bg-zinc-100 dark:bg-zinc-800 rounded-xl w-fit">
        @foreach(['app' => 'fa-mobile-alt', 'admin' => 'fa-shield-alt', 'home' => 'fa-globe'] as $pan => $ico)
        <button wire:click="switchPanel('{{ $pan }}')"
                class="flex items-center gap-2 px-4 py-2 text-sm font-semibold rounded-lg transition
                       {{ $activePanel === $pan
                          ? 'bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white shadow-sm'
                          : 'text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200' }}">
            <i class="fas {{ $ico }} text-xs"></i>
            {{ __('admin_ui.menu.panels.'.$pan) }}
        </button>
        @endforeach
    </div>

    {{-- Top actions --}}
    <div class="mb-4 flex items-center justify-between">
        <p class="text-sm text-zinc-500 dark:text-zinc-400">
            {{ __('admin_ui.menu.items_in_panel', ['count' => count($items), 'panel' => __('admin_ui.menu.panels.'.$activePanel)]) }}
        </p>
        <div class="flex flex-wrap items-center gap-2">
            <button
                type="button"
                wire:click="exportExcel"
                class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-semibold bg-amber-500 hover:bg-amber-600 text-white rounded-lg transition"
            >
                <i class="fas fa-file-excel text-xs"></i>
                {{ __('admin_ui.menu.actions.export_excel') }}
            </button>

            <button
                type="button"
                wire:click="openImportExcelModal"
                class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-semibold bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition"
            >
                <i class="fas fa-file-import text-xs"></i>
                {{ __('admin_ui.menu.actions.import_excel') }}
            </button>

            <button
                type="button"
                @click="window.Starcho.confirm({
                    title: @js(__('js.delete.title')),
                    message: @js(__('admin_ui.menu.clear_confirm', ['panel' => __('admin_ui.menu.panels.'.$activePanel)])),
                    okText: @js(__('js.delete.ok')),
                    cancelText: @js(__('js.confirm.cancel')),
                    onConfirm: () => $wire.clearPanelItems(),
                })"
                class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-semibold bg-rose-600 hover:bg-rose-700 text-white rounded-lg transition"
            >
                <i class="fas fa-trash-alt text-xs"></i>
                {{ __('admin_ui.menu.actions.clear_all') }}
            </button>

            <button wire:click="openCreate()"
                    class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-semibold bg-violet-600 hover:bg-violet-700 text-white rounded-lg transition">
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
                {{ __('admin_ui.menu.new_item') }}
            </button>
        </div>
    </div>

    {{-- Section labels manager --}}
    <div class="mb-5 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-4">
        <div class="flex items-center justify-between gap-3 mb-3">
            <p class="text-sm font-semibold text-zinc-800 dark:text-zinc-100">{{ __('admin_ui.menu.labels.title') }}</p>
            <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('admin_ui.menu.labels.subtitle') }}</p>
        </div>

        <form wire:submit="saveSectionLabel" class="flex flex-wrap items-center gap-2 mb-3">
            <input
                wire:model="sectionLabelInput"
                type="text"
                placeholder="{{ __('admin_ui.menu.labels.placeholder') }}"
                class="min-w-60 flex-1 rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-3 py-2 text-sm text-zinc-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-violet-500"
            >

            <button
                type="submit"
                class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-semibold bg-violet-600 hover:bg-violet-700 text-white rounded-lg transition"
            >
                <i class="fas {{ $editingSectionId ? 'fa-pen' : 'fa-plus' }} text-xs"></i>
                {{ $editingSectionId ? __('admin_ui.menu.labels.update') : __('admin_ui.menu.labels.create') }}
            </button>

            @if($editingSectionId)
                <button
                    type="button"
                    wire:click="cancelSectionEdit"
                    class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-semibold bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 rounded-lg transition"
                >
                    {{ __('admin_ui.common.cancel') }}
                </button>
            @endif
        </form>

        <div class="flex flex-wrap gap-2">
            @forelse($sectionLabels as $sectionLabel)
                <button
                    type="button"
                    wire:click="editSectionLabel({{ $sectionLabel['id'] }})"
                    class="inline-flex items-center gap-2 rounded-full border border-violet-200 dark:border-violet-700/50 bg-violet-50 dark:bg-violet-900/20 px-3 py-1.5 text-xs font-medium text-violet-700 dark:text-violet-300 hover:bg-violet-100 dark:hover:bg-violet-900/30 transition"
                    title="{{ __('admin_ui.menu.labels.edit') }}"
                >
                    <i class="fas fa-tag text-[10px]"></i>
                    <span>{{ $sectionLabel['label'] }}</span>
                    <i class="fas fa-pen text-[10px] opacity-70"></i>
                </button>
            @empty
                <p class="text-xs text-zinc-400">{{ __('admin_ui.menu.labels.empty') }}</p>
            @endforelse
        </div>
    </div>

    {{-- Draggable tree --}}
    @if(count($items) > 0)
        <div class="mb-2 text-xs text-zinc-500 dark:text-zinc-400">
            {{ __('admin_ui.menu.drag_hint') }}
        </div>

        <div class="sa-menu-dropzone space-y-2" data-parent-id="" id="menu-root-dropzone">
            @foreach($items as $item)
                @include('livewire.admin.partials.menu-item-row', ['item' => $item, 'depth' => 0])
            @endforeach
        </div>
    @else
        <div class="rounded-xl border border-dashed border-zinc-300 dark:border-zinc-700 p-8 text-center">
            <p class="text-sm text-zinc-400">{{ __('admin_ui.menu.no_items', ['panel' => __('admin_ui.menu.panels.'.$activePanel)]) }}</p>
        </div>
    @endif

    {{-- Modal --}}
    @if($showModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" wire:click="closeModal"></div>
        <div class="relative bg-white dark:bg-zinc-900 rounded-2xl shadow-2xl w-full max-w-md border border-zinc-200 dark:border-zinc-700">
            <div class="flex items-center justify-between px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                <h2 class="text-base font-semibold text-zinc-900 dark:text-white">
                    {{ $editingId ? __('admin_ui.menu.modal.edit_item') : __('admin_ui.menu.modal.new_item') }}
                </h2>
                <button wire:click="closeModal" class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200">
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form wire:submit="save" class="p-6 space-y-4">
                <div class="grid grid-cols-2 gap-4">

                    <div class="col-span-2">
                        <label class="block text-xs font-semibold text-zinc-500 dark:text-zinc-400 mb-1 uppercase tracking-wide">{{ __('admin_ui.menu.modal.name') }} *</label>
                        <input wire:model="name" type="text" placeholder="{{ __('admin_ui.menu.modal.name_placeholder') }}"
                               class="w-full rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-3 py-2 text-sm text-zinc-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-violet-500">
                        @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-zinc-500 dark:text-zinc-400 mb-1 uppercase tracking-wide">{{ __('admin_ui.menu.modal.panel') }} *</label>
                        <select wire:model="panel"
                                class="w-full rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-3 py-2 text-sm text-zinc-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-violet-500">
                            <option value="app">{{ __('admin_ui.menu.panels.app') }}</option>
                            <option value="admin">{{ __('admin_ui.menu.panels.admin') }}</option>
                            <option value="home">{{ __('admin_ui.menu.panels.home') }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-zinc-500 dark:text-zinc-400 mb-1 uppercase tracking-wide">{{ __('admin_ui.menu.modal.section') }}</label>
                        <input wire:model="section" type="text" list="menu-section-options" placeholder="{{ __('admin_ui.menu.modal.section_placeholder') }}"
                               class="w-full rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-3 py-2 text-sm text-zinc-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-violet-500">
                        <datalist id="menu-section-options">
                            @foreach($sectionOptions as $sectionOpt)
                                <option value="{{ $sectionOpt }}"></option>
                            @endforeach
                        </datalist>
                        <p class="mt-1 text-[11px] text-zinc-400">{{ __('admin_ui.menu.modal.section_hint') }}</p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-zinc-500 dark:text-zinc-400 mb-1 uppercase tracking-wide">{{ __('admin_ui.menu.modal.icon') }}</label>
                        <input wire:model="icon" type="text" placeholder="{{ __('admin_ui.menu.modal.icon_placeholder') }}"
                               class="w-full rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-3 py-2 text-sm text-zinc-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-violet-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-zinc-500 dark:text-zinc-400 mb-1 uppercase tracking-wide">{{ __('admin_ui.menu.modal.order') }}</label>
                        <input wire:model="sort_order" type="number" min="0"
                               class="w-full rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-3 py-2 text-sm text-zinc-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-violet-500">
                    </div>

                    <div class="col-span-2">
                        <label class="block text-xs font-semibold text-zinc-500 dark:text-zinc-400 mb-1 uppercase tracking-wide">{{ __('admin_ui.menu.modal.named_route') }}</label>
                        <input wire:model="route" type="text" placeholder="{{ __('admin_ui.menu.modal.named_route_placeholder') }}"
                               class="w-full rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-3 py-2 text-sm text-zinc-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-violet-500">
                    </div>

                    <div class="col-span-2">
                        <label class="block text-xs font-semibold text-zinc-500 dark:text-zinc-400 mb-1 uppercase tracking-wide">{{ __('admin_ui.menu.modal.direct_url') }}</label>
                        <input wire:model="url" type="text" placeholder="{{ __('admin_ui.menu.modal.direct_url_placeholder') }}"
                               class="w-full rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-3 py-2 text-sm text-zinc-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-violet-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-zinc-500 dark:text-zinc-400 mb-1 uppercase tracking-wide">{{ __('admin_ui.menu.modal.parent') }}</label>
                        <select wire:model="parent_id"
                                class="w-full rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-3 py-2 text-sm text-zinc-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-violet-500">
                            <option value="">{{ __('admin_ui.menu.modal.root_option') }}</option>
                            @foreach($topLevelItems as $top)
                                @if($top->id !== $editingId)
                                <option value="{{ $top->id }}">{{ $top->display_name }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-zinc-500 dark:text-zinc-400 mb-1 uppercase tracking-wide">{{ __('admin_ui.menu.modal.target') }}</label>
                        <select wire:model="target"
                                class="w-full rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-3 py-2 text-sm text-zinc-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-violet-500">
                            <option value="_self">_self</option>
                            <option value="_blank">_blank</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-zinc-500 dark:text-zinc-400 mb-1 uppercase tracking-wide">{{ __('admin_ui.menu.modal.module') }}</label>
                        <input wire:model="module_key" type="text" placeholder="{{ __('admin_ui.menu.modal.module_placeholder') }}"
                               class="w-full rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-3 py-2 text-sm text-zinc-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-violet-500">
                    </div>

                    <div class="flex items-center gap-2 pt-5">
                        <input wire:model="active" type="checkbox" id="item-active" class="rounded border-zinc-300 text-violet-600 focus:ring-violet-500">
                        <label for="item-active" class="text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('admin_ui.menu.modal.active') }}</label>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" wire:click="closeModal"
                            class="px-4 py-2 text-sm font-medium text-zinc-700 dark:text-zinc-300 bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 rounded-lg transition">
                        {{ __('admin_ui.common.cancel') }}
                    </button>
                    <button type="submit"
                            class="px-4 py-2 text-sm font-medium text-white bg-violet-600 hover:bg-violet-700 rounded-lg transition">
                        <span wire:loading.remove wire:target="save">{{ __('admin_ui.common.save') }}</span>
                        <span wire:loading wire:target="save">{{ __('admin_ui.common.saving') }}</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    @assets
        <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
    @endassets

    <script>
        (() => {
            let initialized = false;

            function serializeDropzone(dropzone) {
                const nodes = Array.from(dropzone.children).filter((el) => el.classList.contains('sa-menu-node'));

                return nodes.map(node => {
                    const id = Number(node.dataset.id || 0);
                    const childrenDropzone = Array.from(node.children)
                        .find((child) => child.classList && child.classList.contains('sa-menu-children'));

                    return {
                        id,
                        children: childrenDropzone ? serializeDropzone(childrenDropzone) : [],
                    };
                }).filter(node => node.id > 0);
            }

            function setupSortable(dropzone, onEnd) {
                if (!dropzone || dropzone.dataset.sortableReady === '1') {
                    return;
                }

                dropzone.dataset.sortableReady = '1';

                new Sortable(dropzone, {
                    group: 'starcho-menu-tree',
                    animation: 180,
                    fallbackOnBody: true,
                    swapThreshold: 0.65,
                    handle: '.sa-drag-handle',
                    draggable: '.sa-menu-node',
                    onEnd,
                });
            }

            function initMenuDnD() {
                const root = document.querySelector('[data-menu-tree-root]');

                if (!root || typeof Sortable === 'undefined') {
                    return;
                }

                const dropzones = root.querySelectorAll('.sa-menu-dropzone');
                const onEnd = () => {
                    const rootDropzone = root.querySelector('#menu-root-dropzone');
                    if (!rootDropzone) {
                        return;
                    }

                    const tree = serializeDropzone(rootDropzone);
                    Livewire.dispatch('menuTreeReordered', { tree });
                };

                dropzones.forEach(zone => setupSortable(zone, onEnd));
            }

            document.addEventListener('livewire:navigated', initMenuDnD);
            document.addEventListener('menu-dnd-refresh', () => setTimeout(initMenuDnD, 0));

            if (!initialized) {
                initialized = true;
                setTimeout(initMenuDnD, 0);
            }
        })();
    </script>

    <x-starcho-popup-admin-import
        modal-name="modal-admin-menu-import"
        submit-method="importPanel"
        loading-target="importPanel"
        title="{{ __('admin_ui.menu.actions.import') }}"
        file-model="importFile"
        accept=".json,.txt,application/json,text/plain"
    />

    <x-starcho-popup-admin-import
        modal-name="modal-admin-menu-import-excel"
        submit-method="importExcel"
        loading-target="importExcel"
        title="{{ __('admin_ui.menu.actions.import_excel') }}"
        file-model="importExcelFile"
    />

</div>
