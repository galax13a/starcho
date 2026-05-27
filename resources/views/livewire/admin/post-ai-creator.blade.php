<div>
    <x-starcho-popup-standar
        name="modal-post-ai-creator"
        width="md:w-[820px]"
        submit-action="create"
        title="Crear post con AI"
        subtitle="Describe el artículo y AI generará contenido, extracto, SEO y Open Graph en los idiomas activos."
        save-label="Crear post"
        saving-label="Creando..."
        loading-target="create"
    >
        <div class="space-y-4">
            @unless($settings->enabled && $settings->hasAnyProviderKey())
                <div class="rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-700 dark:border-amber-700/50 dark:bg-amber-900/20 dark:text-amber-200">
                    Activa AI y guarda al menos una llave en <a href="{{ route('admin.site.index', ['tab' => 'ai']) }}" class="font-semibold underline">admin/site > AI</a>.
                </div>
            @endunless

            @if($errorMessage)
                <div class="rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-700 dark:border-rose-700/50 dark:bg-rose-900/20 dark:text-rose-200">{{ $errorMessage }}</div>
            @endif

            <flux:field>
                <flux:label>Descripción del post</flux:label>
                <textarea wire:model="description" rows="8" maxlength="5000" placeholder="Ej: artículo sobre cómo migrar Laravel a Cloudflare R2, con pasos, errores comunes y buenas prácticas."
                    class="w-full resize-none rounded-xl border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white"></textarea>
                <flux:error name="description" />
            </flux:field>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <flux:field>
                    <flux:label>Proveedor</flux:label>
                    <select wire:model.live="provider" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white">
                        @foreach($providers as $providerKey => $providerLabel)
                            <option value="{{ $providerKey }}">{{ $providerLabel }}</option>
                        @endforeach
                    </select>
                </flux:field>
                <flux:field>
                    <flux:label>Modelo</flux:label>
                    <input list="post-ai-models" wire:model="model" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white">
                    <datalist id="post-ai-models">
                        @foreach($models as $modelName)
                            <option value="{{ $modelName }}"></option>
                        @endforeach
                    </datalist>
                </flux:field>
                <flux:field>
                    <flux:label>Estado</flux:label>
                    <select wire:model="status" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white">
                        <option value="draft">Borrador</option>
                        <option value="published">Publicado</option>
                        <option value="private">Privado</option>
                    </select>
                </flux:field>
                <flux:field>
                    <flux:label>Autor</flux:label>
                    <select wire:model="authorId" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white">
                        @foreach($this->authors as $author)
                            <option value="{{ $author->id }}">{{ $author->name }}</option>
                        @endforeach
                    </select>
                </flux:field>
            </div>

            <label class="flex cursor-pointer items-center gap-3 rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-700 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200">
                <input type="checkbox" wire:model="allowComments" class="size-4 rounded border-zinc-300 text-violet-600 focus:ring-violet-500">
                Permitir comentarios
            </label>
        </div>
    </x-starcho-popup-standar>
</div>
