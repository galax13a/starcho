<div>
    <x-starcho-popup-standar
        name="modal-media-tags"
        width="md:w-[760px]"
        submit-action="saveTags"
        title="Etiquetar archivo"
        save-label="Guardar tags"
        saving-label="Guardando..."
        loading-target="saveTags"
    >
        <div class="rounded-lg border border-zinc-200 bg-zinc-50 px-3 py-2 text-sm text-zinc-600 dark:border-zinc-700 dark:bg-zinc-800/60 dark:text-zinc-300">
            <span class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $mediaName ?: 'Archivo' }}</span>
        </div>

        <flux:field>
            <flux:label>Buscar tags</flux:label>
            <flux:input wire:model.live.debounce.250ms="search" placeholder="Buscar por nombre..." />
        </flux:field>

        <div class="max-h-64 overflow-y-auto rounded-xl border border-zinc-200 p-3 dark:border-zinc-700">
            <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                @forelse($this->popularTags as $tag)
                    <label class="flex cursor-pointer items-center justify-between gap-3 rounded-lg border border-zinc-200 px-3 py-2 text-sm transition hover:bg-zinc-50 dark:border-zinc-700 dark:hover:bg-zinc-800">
                        <span class="min-w-0 truncate text-zinc-700 dark:text-zinc-200">#{{ $tag->name }}</span>
                        <span class="flex shrink-0 items-center gap-2">
                            <span class="rounded bg-zinc-100 px-1.5 py-0.5 text-[10px] text-zinc-500 dark:bg-zinc-800">{{ $tag->usage_count }}</span>
                            <input type="checkbox" wire:model="selectedTags" value="{{ $tag->id }}" class="rounded border-zinc-300 text-violet-600 focus:ring-violet-500">
                        </span>
                    </label>
                @empty
                    <p class="col-span-full py-6 text-center text-sm text-zinc-500">No hay tags todavía.</p>
                @endforelse
            </div>
        </div>

        <flux:field>
            <flux:label>Crear nuevos tags</flux:label>
            <flux:input wire:model="newTags" placeholder="Separados por coma: home, banner, producto" />
            <flux:error name="newTags" />
        </flux:field>
    </x-starcho-popup-standar>
</div>
