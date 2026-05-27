<div>
    <x-starcho-popup-standar
        name="modal-page-ai-creator"
        width="md:w-[880px]"
        submit-action="create"
        title="Crear página con AI"
        subtitle="Describe la página y AI generará contenido, extracto, SEO y Open Graph en los idiomas activos."
        save-label="Crear página"
        saving-label="Creando..."
        loading-target="create"
        form-class="space-y-5"
    >
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-[1fr_280px]">
            <div class="space-y-4">
                @unless($settings->enabled && $settings->hasAnyProviderKey())
                    <div class="rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-700 dark:border-amber-700/50 dark:bg-amber-900/20 dark:text-amber-200">
                        Activa AI y guarda al menos una llave en <a href="{{ route('admin.site.index', ['tab' => 'ai']) }}" class="font-semibold underline">admin/site > AI</a>.
                    </div>
                @endunless

                @if($errorMessage)
                    <div class="rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-700 dark:border-rose-700/50 dark:bg-rose-900/20 dark:text-rose-200">
                        {{ $errorMessage }}
                    </div>
                @endif

                <flux:field>
                    <flux:label>Descripción de la página</flux:label>
                    <textarea
                        wire:model="description"
                        rows="9"
                        maxlength="5000"
                        placeholder="Ej: crea una página de servicios para una agencia de desarrollo Laravel. Debe explicar beneficios, proceso, paquetes, preguntas frecuentes y llamada a la acción."
                        class="w-full resize-none rounded-xl border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 outline-none transition placeholder:text-zinc-400 focus:border-violet-400 focus:ring-2 focus:ring-violet-100 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white dark:placeholder:text-zinc-600 dark:focus:ring-violet-900/30"
                    ></textarea>
                    <flux:error name="description" />
                </flux:field>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <flux:field>
                        <flux:label>Proveedor</flux:label>
                        <select wire:model.live="provider" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 outline-none transition focus:border-violet-400 focus:ring-2 focus:ring-violet-100 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white dark:focus:ring-violet-900/30">
                            @forelse($providers as $providerKey => $providerLabel)
                                <option value="{{ $providerKey }}">{{ $providerLabel }}</option>
                            @empty
                                <option value="openai">Sin proveedor configurado</option>
                            @endforelse
                        </select>
                        <flux:error name="provider" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Modelo</flux:label>
                        <input list="page-ai-creator-models" wire:model="model" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 outline-none transition focus:border-violet-400 focus:ring-2 focus:ring-violet-100 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white dark:focus:ring-violet-900/30">
                        <datalist id="page-ai-creator-models">
                            @foreach($models as $modelName)
                                <option value="{{ $modelName }}"></option>
                            @endforeach
                        </datalist>
                        <flux:error name="model" />
                    </flux:field>
                </div>
            </div>

            <div class="space-y-4 rounded-xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-800 dark:bg-zinc-900/70">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400">Idiomas</p>
                    <div class="mt-2 flex flex-wrap gap-1.5">
                        @foreach($this->languages as $locale)
                            <span class="rounded-lg bg-white px-2.5 py-1 text-xs font-semibold uppercase text-violet-700 ring-1 ring-zinc-200 dark:bg-zinc-800 dark:text-violet-300 dark:ring-zinc-700">{{ $locale }}</span>
                        @endforeach
                    </div>
                </div>

                <flux:field>
                    <flux:label>Estado</flux:label>
                    <select wire:model="status" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 outline-none transition focus:border-violet-400 focus:ring-2 focus:ring-violet-100 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white">
                        <option value="draft">Borrador</option>
                        <option value="published">Publicado</option>
                        <option value="private">Privado</option>
                    </select>
                    <flux:error name="status" />
                </flux:field>

                <flux:field>
                    <flux:label>Autor</flux:label>
                    <select wire:model="authorId" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 outline-none transition focus:border-violet-400 focus:ring-2 focus:ring-violet-100 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white">
                        @foreach($this->authors as $author)
                            <option value="{{ $author->id }}">{{ $author->name }}</option>
                        @endforeach
                    </select>
                    <flux:error name="authorId" />
                </flux:field>

                <flux:field>
                    <flux:label>Menú</flux:label>
                    <select wire:model="navPosition" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 outline-none transition focus:border-violet-400 focus:ring-2 focus:ring-violet-100 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white">
                        <option value="none">No mostrar</option>
                        <option value="header">Header</option>
                        <option value="footer">Footer</option>
                        <option value="both">Header y Footer</option>
                    </select>
                    <flux:error name="navPosition" />
                </flux:field>

                <div class="grid grid-cols-2 gap-3">
                    <flux:field>
                        <flux:label>Orden</flux:label>
                        <input type="number" min="0" wire:model="menuOrder" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 outline-none transition focus:border-violet-400 focus:ring-2 focus:ring-violet-100 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white">
                        <flux:error name="menuOrder" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Padre</flux:label>
                        <select wire:model="parentId" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 outline-none transition focus:border-violet-400 focus:ring-2 focus:ring-violet-100 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white">
                            <option value="0">Sin padre</option>
                            @foreach($this->parentPages as $page)
                                <option value="{{ $page->id }}">{{ $page->getTranslation('title', $this->languages[0] ?? 'es', false) ?: $page->title }}</option>
                            @endforeach
                        </select>
                        <flux:error name="parentId" />
                    </flux:field>
                </div>

                <label class="flex cursor-pointer items-center gap-3 rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-700 transition hover:border-violet-200 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200">
                    <input type="checkbox" wire:model="allowComments" class="size-4 rounded border-zinc-300 text-violet-600 focus:ring-violet-500">
                    Permitir comentarios
                </label>
            </div>
        </div>
    </x-starcho-popup-standar>
</div>
