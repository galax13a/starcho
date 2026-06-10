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

                <div class="flex justify-end">
                    <button type="button" wire:click="resetUiState"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-200 px-3 py-1.5 text-xs font-semibold text-zinc-500 transition hover:border-violet-200 hover:bg-violet-50 hover:text-violet-700 dark:border-zinc-700 dark:text-zinc-400 dark:hover:bg-violet-950/20 dark:hover:text-violet-300">
                        <i class="fas fa-rotate-left text-[10px]"></i>
                        Restablecer ajustes
                    </button>
                </div>

                <flux:field>
                    <flux:label>Descripción de la página</flux:label>
                    <textarea
                        wire:model.live.debounce.500ms="description"
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
                        <select wire:model="model" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 outline-none transition focus:border-violet-400 focus:ring-2 focus:ring-violet-100 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white dark:focus:ring-violet-900/30">
                            @foreach($models as $modelName)
                                <option value="{{ $modelName }}">{{ $modelName }}</option>
                            @endforeach
                        </select>
                        <flux:error name="model" />
                    </flux:field>
                </div>

                <flux:field>
                    <flux:label>Formato del contenido</flux:label>
                    <div class="grid gap-2 sm:grid-cols-2">
                        <label class="cursor-pointer rounded-xl border p-3 text-sm transition {{ $contentFormat === 'editorjs' ? 'border-violet-400 bg-violet-50 text-violet-800 dark:bg-violet-900/20 dark:text-violet-100' : 'border-zinc-200 text-zinc-600 hover:border-zinc-300 dark:border-zinc-700 dark:text-zinc-300' }}">
                            <input type="radio" wire:model.live="contentFormat" value="editorjs" class="sr-only">
                            <span class="block font-semibold">Editor.js</span>
                            <span class="mt-1 block text-xs opacity-75">Bloques nativos y fáciles de editar.</span>
                        </label>
                        <label class="cursor-pointer rounded-xl border p-3 text-sm transition {{ $contentFormat === 'html' ? 'border-violet-400 bg-violet-50 text-violet-800 dark:bg-violet-900/20 dark:text-violet-100' : 'border-zinc-200 text-zinc-600 hover:border-zinc-300 dark:border-zinc-700 dark:text-zinc-300' }}">
                            <input type="radio" wire:model.live="contentFormat" value="html" class="sr-only">
                            <span class="block font-semibold">HTML + Tailwind</span>
                            <span class="mt-1 block text-xs opacity-75">Landing visual en Starcho HTML.</span>
                        </label>
                    </div>
                    <flux:error name="contentFormat" />
                </flux:field>
            </div>

            <div class="space-y-4 rounded-xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-800 dark:bg-zinc-900/70">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400">Idiomas</p>
                    <div class="mt-2 grid gap-2">
                        <label class="cursor-pointer rounded-xl border p-3 text-sm transition {{ $languageMode === 'single' ? 'border-sky-400 bg-sky-50 text-sky-800 dark:bg-sky-950/30 dark:text-sky-100' : 'border-zinc-200 bg-white text-zinc-600 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-300' }}">
                            <input type="radio" wire:model.live="languageMode" value="single" class="sr-only">
                            <span class="block font-semibold">Solo un idioma</span>
                            <span class="mt-1 block text-xs opacity-75">Más rápido para crear primero una versión.</span>
                        </label>
                        <label class="cursor-pointer rounded-xl border p-3 text-sm transition {{ $languageMode === 'multi' ? 'border-sky-400 bg-sky-50 text-sky-800 dark:bg-sky-950/30 dark:text-sky-100' : 'border-zinc-200 bg-white text-zinc-600 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-300' }}">
                            <input type="radio" wire:model.live="languageMode" value="multi" class="sr-only">
                            <span class="block font-semibold">Multi idioma</span>
                            <span class="mt-1 block text-xs opacity-75">Llena todos los idiomas activos.</span>
                        </label>
                    </div>

                    @if($languageMode === 'single')
                        <select wire:model.live="selectedLocale" class="mt-2 w-full rounded-lg border border-sky-200 bg-white px-3 py-2 text-sm font-semibold uppercase text-zinc-900 dark:border-sky-900/60 dark:bg-zinc-950 dark:text-white">
                            @foreach($this->languages as $locale)
                                <option value="{{ $locale }}">{{ $locale }}</option>
                            @endforeach
                        </select>
                    @else
                        <div class="mt-2 flex flex-wrap gap-1.5">
                            @foreach($this->languages as $locale)
                                <span class="rounded-lg bg-white px-2.5 py-1 text-xs font-semibold uppercase text-violet-700 ring-1 ring-zinc-200 dark:bg-zinc-800 dark:text-violet-300 dark:ring-zinc-700">{{ $locale }}</span>
                            @endforeach
                        </div>
                    @endif
                    <div class="mt-1">
                        <flux:error name="languageMode" />
                        <flux:error name="selectedLocale" />
                    </div>
                </div>

                <div class="rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-800 dark:border-amber-900/60 dark:bg-amber-950/20 dark:text-amber-200">
                    Se guardará automáticamente como borrador para revisión editorial.
                </div>

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

                @php($selectedProfile = $articleProfiles[$articleSize] ?? $articleProfiles['medium'])
                <div class="rounded-2xl border border-violet-200 bg-white p-3 dark:border-violet-900/60 dark:bg-zinc-950">
                    <div class="mb-3">
                        <p class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Prompt editorial</p>
                        <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">{{ number_format($selectedProfile['words']) }} palabras · {{ $selectedProfile['reading'] }} min estimados</p>
                    </div>

                    <textarea wire:model.live.debounce.500ms="editorialPrompt" rows="5" maxlength="3000"
                        class="w-full resize-y rounded-xl border border-zinc-200 bg-zinc-50 px-3 py-2 text-sm leading-6 text-zinc-900 outline-none transition focus:border-violet-300 focus:ring-2 focus:ring-violet-100 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white"></textarea>
                    <flux:error name="editorialPrompt" />

                    <div class="mt-3 grid gap-3">
                        <flux:field>
                            <flux:label>Tamaño</flux:label>
                            <select wire:model.live="articleSize" class="w-full rounded-lg border border-zinc-200 bg-zinc-50 px-3 py-2 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white">
                                @foreach($articleProfiles as $key => $profile)
                                    <option value="{{ $key }}">{{ $profile['label'] }} · {{ number_format($profile['words']) }} palabras · {{ $profile['reading'] }} min</option>
                                @endforeach
                            </select>
                            <flux:error name="articleSize" />
                        </flux:field>

                        <flux:field>
                            <flux:label>Tokens máximos</flux:label>
                            <select wire:model.live="maxTokens" class="w-full rounded-lg border border-zinc-200 bg-zinc-50 px-3 py-2 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white">
                                @foreach([1200, 2200, 3600, 5600, 8000] as $tokenOption)
                                    <option value="{{ $tokenOption }}">{{ number_format($tokenOption) }} tokens</option>
                                @endforeach
                            </select>
                            <flux:error name="maxTokens" />
                        </flux:field>
                    </div>
                </div>
            </div>
        </div>

        <div
            x-data="{
                show: false,
                copied: false,
                copyPrompt() {
                    navigator.clipboard.writeText(this.$refs.finalPrompt.innerText).then(() => {
                        this.copied = true;
                        window.Starcho?.notify?.('success', 'Prompt final copiado.');
                        setTimeout(() => this.copied = false, 1400);
                    });
                }
            }"
            class="rounded-2xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-800 dark:bg-zinc-900/70"
        >
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="text-sm font-semibold text-zinc-950 dark:text-white">Prompt final que se enviará</p>
                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Incluye objetivo, instrucción editorial, formato, tamaño y tokens.</p>
                </div>
                <div class="inline-flex overflow-hidden rounded-lg border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-950">
                    <button type="button" @click="show = !show" class="inline-flex h-9 items-center gap-2 px-3 text-xs font-semibold text-zinc-700 transition hover:bg-zinc-100 dark:text-zinc-200 dark:hover:bg-zinc-800">
                        <i class="fas fa-eye text-[11px]"></i>
                        <span x-text="show ? 'Ocultar prompt' : 'Mostrar prompt'"></span>
                    </button>
                    <button type="button" @click="copyPrompt()" class="inline-flex h-9 items-center gap-2 border-l border-zinc-200 px-3 text-xs font-semibold text-violet-700 transition hover:bg-violet-50 dark:border-zinc-700 dark:text-violet-300 dark:hover:bg-violet-950/30">
                        <i class="fas fa-copy text-[11px]"></i>
                        <span x-text="copied ? 'Copiado' : 'Copiar'"></span>
                    </button>
                </div>
            </div>

            <pre x-show="show" x-cloak x-ref="finalPrompt" class="mt-4 max-h-72 overflow-auto whitespace-pre-wrap rounded-xl bg-zinc-950 p-4 text-xs leading-5 text-zinc-100">{{ $this->finalPrompt }}</pre>
            </div>

            {{-- Featured image with AI --}}
            <div class="rounded-2xl border border-fuchsia-200 bg-fuchsia-50/50 p-4 dark:border-fuchsia-900/50 dark:bg-fuchsia-950/20">
                <label class="flex cursor-pointer items-center gap-3 text-sm font-semibold text-fuchsia-900 dark:text-fuchsia-100">
                    <input type="checkbox" wire:model.live="genImage" class="size-4 rounded border-fuchsia-300 text-fuchsia-600 focus:ring-fuchsia-500">
                    <i class="fas fa-image"></i> Generar imagen destacada con IA
                </label>

                @if($genImage)
                    <p class="mt-2 text-xs text-fuchsia-700/80 dark:text-fuchsia-200/70">
                        Proveedor: <strong>{{ \App\Models\AiSetting::IMAGE_PROVIDERS[$settings->image_provider] ?? 'OpenAI' }}</strong> ·
                        Modelo: <strong>{{ $settings->image_model }}</strong>
                    </p>

                    <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <flux:field>
                            <flux:label>Fuente</flux:label>
                            <select wire:model.live="imageMode" class="w-full rounded-lg border border-fuchsia-200 bg-white px-3 py-2 text-sm dark:border-fuchsia-900/60 dark:bg-zinc-950 dark:text-white">
                                <option value="article">A partir de la página</option>
                                <option value="prompt">Desde un prompt</option>
                            </select>
                        </flux:field>
                        <flux:field>
                            <flux:label>Tamaño</flux:label>
                            <select wire:model.live="imageSizePreset" class="w-full rounded-lg border border-fuchsia-200 bg-white px-3 py-2 text-sm dark:border-fuchsia-900/60 dark:bg-zinc-950 dark:text-white">
                                <option value="800x600">800 × 600</option>
                                <option value="480x360">480 × 360</option>
                                <option value="custom">Personalizada…</option>
                            </select>
                        </flux:field>
                    </div>

                    @if($imageSizePreset === 'custom')
                        <div class="mt-2 flex items-center gap-2">
                            <input type="number" wire:model="imgCustomW" min="64" max="2048" class="w-24 rounded-lg border border-fuchsia-200 bg-white px-2 py-1.5 text-sm dark:border-fuchsia-900/60 dark:bg-zinc-950 dark:text-white" placeholder="Ancho">
                            <span class="text-zinc-400">×</span>
                            <input type="number" wire:model="imgCustomH" min="64" max="2048" class="w-24 rounded-lg border border-fuchsia-200 bg-white px-2 py-1.5 text-sm dark:border-fuchsia-900/60 dark:bg-zinc-950 dark:text-white" placeholder="Alto">
                        </div>
                    @endif

                    @if($imageMode === 'prompt')
                        <textarea wire:model="imagePrompt" rows="2" class="mt-2 w-full resize-y rounded-xl border border-fuchsia-200 bg-white px-3 py-2 text-sm dark:border-fuchsia-900/60 dark:bg-zinc-950 dark:text-white" placeholder="Describe la imagen destacada..."></textarea>
                    @else
                        <p class="mt-2 text-xs text-zinc-500 dark:text-zinc-400">Se generará a partir del título de la página.</p>
                    @endif
                @endif
            </div>

        <x-slot:actions>
            @if($errorMessage)
                <div class="order-first w-full rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-700 dark:border-rose-700/50 dark:bg-rose-900/20 dark:text-rose-200">
                    {{ $errorMessage }}
                </div>
            @endif

            <button
                type="submit"
                x-data="{
                    elapsed: 0,
                    timer: null,
                    startTimer() {
                        this.elapsed = 0;
                        clearInterval(this.timer);
                        this.timer = setInterval(() => this.elapsed++, 1000);
                    },
                    stopTimer() {
                        clearInterval(this.timer);
                        this.timer = null;
                    },
                    elapsedLabel() {
                        const minutes = Math.floor(this.elapsed / 60);
                        const seconds = this.elapsed % 60;
                        return minutes > 0 ? `${minutes}m ${seconds.toString().padStart(2, '0')}s` : `${seconds}s`;
                    }
                }"
                x-init="if (window.Livewire) Livewire.hook('commit', ({ succeed, fail }) => { succeed(() => stopTimer()); fail(() => stopTimer()); })"
                @click="startTimer()"
                class="relative inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-[var(--color-accent)] px-4 text-sm font-medium text-[var(--color-accent-foreground)] shadow-sm transition hover:bg-[color-mix(in_oklab,_var(--color-accent),_transparent_10%)] disabled:pointer-events-none disabled:opacity-75"
                wire:loading.attr="disabled"
                wire:target="create"
            >
                <i wire:loading.remove wire:target="create" class="fas fa-wand-magic-sparkles text-xs"></i>
                <svg wire:loading wire:target="create" class="size-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span wire:loading.remove wire:target="create">Crear página</span>
                <span wire:loading wire:target="create" x-text="`Creando... ${elapsedLabel()} / máx {{ config('starcho_ai.request_timeout', 120) }}s`">Creando...</span>
            </button>
        </x-slot:actions>
    </x-starcho-popup-standar>
</div>
