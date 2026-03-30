<div>

    {{-- Panel tabs --}}
    <div class="flex items-center gap-1 mb-5 p-1 bg-zinc-100 dark:bg-zinc-800 rounded-xl w-fit">
        @foreach(['app' => 'fa-mobile-alt', 'admin' => 'fa-shield-alt', 'home' => 'fa-globe'] as $pan => $ico)
        <button wire:click="switchPanel('{{ $pan }}')"
                class="flex items-center gap-2 px-4 py-2 text-sm font-semibold rounded-lg transition
                       {{ $activePanel === $pan
                          ? 'bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white shadow-sm'
                          : 'text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200' }}">
            <i class="fas {{ $ico }} text-xs"></i>
            {{ ucfirst($pan) }}
        </button>
        @endforeach
    </div>

    {{-- Top actions --}}
    <div class="mb-4 flex items-center justify-between">
        <p class="text-sm text-zinc-500 dark:text-zinc-400">
            {{ count($items) }} ítem(s) en panel <strong>{{ $activePanel }}</strong>
        </p>
        <button wire:click="openCreate()"
                class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-semibold bg-violet-600 hover:bg-violet-700 text-white rounded-lg transition">
            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
            Nuevo ítem
        </button>
    </div>

    {{-- Tree grouped by section --}}
    @php
        $grouped = collect($items)->groupBy(fn($i) => $i['section'] ?? '');
    @endphp

    @forelse($grouped as $section => $sectionItems)
    <div class="mb-6">
        @if($section)
        <div class="flex items-center gap-2 mb-2">
            <span class="text-xs font-bold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">{{ $section }}</span>
            <div class="flex-1 h-px bg-zinc-200 dark:bg-zinc-700"></div>
        </div>
        @endif

        <div class="space-y-2">
            @foreach($sectionItems as $item)
                @include('livewire.admin.partials.menu-item-row', ['item' => $item, 'depth' => 0])
            @endforeach
        </div>
    </div>
    @empty
        <div class="rounded-xl border border-dashed border-zinc-300 dark:border-zinc-700 p-8 text-center">
            <p class="text-sm text-zinc-400">No hay ítems en el panel <strong>{{ $activePanel }}</strong>. Crea el primero.</p>
        </div>
    @endforelse

    {{-- Modal --}}
    @if($showModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" wire:click="closeModal"></div>
        <div class="relative bg-white dark:bg-zinc-900 rounded-2xl shadow-2xl w-full max-w-md border border-zinc-200 dark:border-zinc-700">
            <div class="flex items-center justify-between px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                <h2 class="text-base font-semibold text-zinc-900 dark:text-white">
                    {{ $editingId ? 'Editar ítem' : 'Nuevo ítem de menú' }}
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
                        <label class="block text-xs font-semibold text-zinc-500 dark:text-zinc-400 mb-1 uppercase tracking-wide">Nombre *</label>
                        <input wire:model="name" type="text" placeholder="Ej. Mis Tareas"
                               class="w-full rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-3 py-2 text-sm text-zinc-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-violet-500">
                        @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-zinc-500 dark:text-zinc-400 mb-1 uppercase tracking-wide">Panel *</label>
                        <select wire:model="panel"
                                class="w-full rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-3 py-2 text-sm text-zinc-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-violet-500">
                            <option value="app">app</option>
                            <option value="admin">admin</option>
                            <option value="home">home</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-zinc-500 dark:text-zinc-400 mb-1 uppercase tracking-wide">Sección</label>
                        <input wire:model="section" type="text" placeholder="Acceso, Sistema…"
                               class="w-full rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-3 py-2 text-sm text-zinc-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-violet-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-zinc-500 dark:text-zinc-400 mb-1 uppercase tracking-wide">Icono (FA class)</label>
                        <input wire:model="icon" type="text" placeholder="fas fa-home"
                               class="w-full rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-3 py-2 text-sm text-zinc-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-violet-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-zinc-500 dark:text-zinc-400 mb-1 uppercase tracking-wide">Orden</label>
                        <input wire:model="sort_order" type="number" min="0"
                               class="w-full rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-3 py-2 text-sm text-zinc-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-violet-500">
                    </div>

                    <div class="col-span-2">
                        <label class="block text-xs font-semibold text-zinc-500 dark:text-zinc-400 mb-1 uppercase tracking-wide">Ruta nombrada (route)</label>
                        <input wire:model="route" type="text" placeholder="app.tasks.index"
                               class="w-full rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-3 py-2 text-sm text-zinc-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-violet-500">
                    </div>

                    <div class="col-span-2">
                        <label class="block text-xs font-semibold text-zinc-500 dark:text-zinc-400 mb-1 uppercase tracking-wide">URL directa (alternativa)</label>
                        <input wire:model="url" type="text" placeholder="https://..."
                               class="w-full rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-3 py-2 text-sm text-zinc-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-violet-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-zinc-500 dark:text-zinc-400 mb-1 uppercase tracking-wide">Padre</label>
                        <select wire:model="parent_id"
                                class="w-full rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-3 py-2 text-sm text-zinc-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-violet-500">
                            <option value="">— Raíz —</option>
                            @foreach($topLevelItems as $top)
                                @if($top->id !== $editingId)
                                <option value="{{ $top->id }}">{{ $top->display_name }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-zinc-500 dark:text-zinc-400 mb-1 uppercase tracking-wide">Target</label>
                        <select wire:model="target"
                                class="w-full rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-3 py-2 text-sm text-zinc-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-violet-500">
                            <option value="_self">_self</option>
                            <option value="_blank">_blank</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-zinc-500 dark:text-zinc-400 mb-1 uppercase tracking-wide">Módulo</label>
                        <input wire:model="module_key" type="text" placeholder="tasks, contacts…"
                               class="w-full rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-3 py-2 text-sm text-zinc-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-violet-500">
                    </div>

                    <div class="flex items-center gap-2 pt-5">
                        <input wire:model="active" type="checkbox" id="item-active" class="rounded border-zinc-300 text-violet-600 focus:ring-violet-500">
                        <label for="item-active" class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Activo</label>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" wire:click="closeModal"
                            class="px-4 py-2 text-sm font-medium text-zinc-700 dark:text-zinc-300 bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 rounded-lg transition">
                        Cancelar
                    </button>
                    <button type="submit"
                            class="px-4 py-2 text-sm font-medium text-white bg-violet-600 hover:bg-violet-700 rounded-lg transition">
                        <span wire:loading.remove wire:target="save">Guardar</span>
                        <span wire:loading wire:target="save">Guardando…</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

</div>
