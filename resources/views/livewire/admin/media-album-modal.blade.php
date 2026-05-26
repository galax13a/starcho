<div>
    <x-starcho-popup-standar
        name="modal-media-album"
        width="md:w-[680px]"
        submit-action="saveAlbum"
        :title="$albumId > 0 ? 'Editar álbum' : 'Nuevo álbum'"
        :save-label="$albumId > 0 ? 'Guardar cambios' : 'Crear álbum'"
        saving-label="Guardando..."
        loading-target="saveAlbum"
    >
        <flux:field>
            <flux:label>Nombre del álbum</flux:label>
            <flux:input wire:model="name" placeholder="Ej. Campaña principal" />
            <flux:error name="name" />
        </flux:field>

        <flux:field>
            <flux:label>Descripción</flux:label>
            <flux:textarea wire:model="description" rows="3" placeholder="Notas internas o descripción pública del álbum" />
            <flux:error name="description" />
        </flux:field>

        <flux:field>
            <flux:label>Tags</flux:label>
            <flux:input wire:model="tags" placeholder="landing, producto, banners" />
            <flux:error name="tags" />
        </flux:field>

        <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-3 dark:border-zinc-700 dark:bg-zinc-800/60">
            <label class="flex items-center gap-2 text-sm font-medium text-zinc-700 dark:text-zinc-200">
                <input type="checkbox" wire:model.live="passwordEnabled" class="rounded border-zinc-300 text-violet-600 focus:ring-violet-500">
                Requiere password para verlo
            </label>

            @if($passwordEnabled)
                <div class="mt-3">
                    <flux:field>
                        <flux:label>{{ $albumId > 0 ? 'Nuevo password opcional' : 'Password' }}</flux:label>
                        <flux:input wire:model="password" type="password" placeholder="{{ $albumId > 0 ? 'Dejar vacío para mantener el actual' : 'Password del álbum' }}" />
                        <flux:error name="password" />
                    </flux:field>
                </div>
            @endif
        </div>
    </x-starcho-popup-standar>
</div>
