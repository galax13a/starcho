<div>
    <x-starcho-popup-standar
        name="modal-post-ai-creator"
        width="md:w-[900px]"
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

            <flux:field>
                <flux:label>Descripción del post</flux:label>
                <textarea wire:model.live.debounce.500ms="description" rows="8" maxlength="5000" placeholder="Ej: artículo sobre cómo migrar Laravel a Cloudflare R2, con pasos, errores comunes y buenas prácticas."
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
                    <select wire:model="model" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white">
                        @foreach($models as $modelName)
                            <option value="{{ $modelName }}">{{ $modelName }}</option>
                        @endforeach
                    </select>
                </flux:field>
                <div class="rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-800 dark:border-amber-900/60 dark:bg-amber-950/20 dark:text-amber-200">
                    Se guardará automáticamente como borrador para revisión editorial.
                </div>
                <flux:field>
                    <flux:label>Autor</flux:label>
                    <select wire:model="authorId" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white">
                        @foreach($this->authors as $author)
                            <option value="{{ $author->id }}">{{ $author->name }}</option>
                        @endforeach
                    </select>
                </flux:field>
            </div>

            <flux:field>
                <flux:label>Formato del contenido</flux:label>
                <div class="grid gap-2 sm:grid-cols-2">
                    <label class="cursor-pointer rounded-xl border p-3 text-sm transition {{ $contentFormat === 'editorjs' ? 'border-violet-400 bg-violet-50 text-violet-800 dark:bg-violet-900/20 dark:text-violet-100' : 'border-zinc-200 text-zinc-600 hover:border-zinc-300 dark:border-zinc-700 dark:text-zinc-300' }}">
                        <input type="radio" wire:model.live="contentFormat" value="editorjs" class="sr-only">
                        <span class="block font-semibold">Editor.js</span>
                        <span class="mt-1 block text-xs opacity-75">Texto editable por bloques.</span>
                    </label>
                    <label class="cursor-pointer rounded-xl border p-3 text-sm transition {{ $contentFormat === 'html' ? 'border-violet-400 bg-violet-50 text-violet-800 dark:bg-violet-900/20 dark:text-violet-100' : 'border-zinc-200 text-zinc-600 hover:border-zinc-300 dark:border-zinc-700 dark:text-zinc-300' }}">
                        <input type="radio" wire:model.live="contentFormat" value="html" class="sr-only">
                        <span class="block font-semibold">HTML + Tailwind</span>
                        <span class="mt-1 block text-xs opacity-75">Diseño rico en Starcho HTML.</span>
                    </label>
                </div>
                <flux:error name="contentFormat" />
            </flux:field>

            <label class="flex cursor-pointer items-center gap-3 rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-700 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200">
                <input type="checkbox" wire:model="allowComments" class="size-4 rounded border-zinc-300 text-violet-600 focus:ring-violet-500">
                Permitir comentarios
            </label>

            @php($selectedProfile = $articleProfiles[$articleSize] ?? $articleProfiles['medium'])
            <div class="rounded-2xl border border-violet-200 bg-violet-50/60 p-4 dark:border-violet-900/50 dark:bg-violet-950/20">
                <div class="mb-3 flex items-center justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold text-violet-950 dark:text-violet-100">Prompt editorial profesional</p>
                        <p class="text-xs text-violet-700/80 dark:text-violet-200/70">Puedes ajustar esta guía para que AI escriba con más intención técnica y visual.</p>
                    </div>
                    <span class="shrink-0 rounded-full bg-white px-2.5 py-1 text-xs font-semibold text-violet-700 ring-1 ring-violet-200 dark:bg-violet-950 dark:text-violet-200 dark:ring-violet-800">
                        {{ number_format($selectedProfile['words']) }} palabras · {{ $selectedProfile['reading'] }} min
                    </span>
                </div>

                <textarea wire:model.live.debounce.500ms="editorialPrompt" rows="4" maxlength="3000"
                    class="w-full resize-y rounded-xl border border-violet-200 bg-white px-3 py-2 text-sm leading-6 text-zinc-900 outline-none transition focus:border-violet-400 focus:ring-2 focus:ring-violet-100 dark:border-violet-900/60 dark:bg-zinc-950 dark:text-white dark:focus:ring-violet-900/30"></textarea>
                <flux:error name="editorialPrompt" />

                <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <flux:field>
                        <flux:label>Tamaño del artículo</flux:label>
                        <select wire:model.live="articleSize" class="w-full rounded-lg border border-violet-200 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-violet-900/60 dark:bg-zinc-950 dark:text-white">
                            @foreach($articleProfiles as $key => $profile)
                                <option value="{{ $key }}">{{ $profile['label'] }} · {{ number_format($profile['words']) }} palabras · {{ $profile['reading'] }} min</option>
                            @endforeach
                        </select>
                        <flux:error name="articleSize" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Tokens máximos</flux:label>
                        <select wire:model.live="maxTokens" class="w-full rounded-lg border border-violet-200 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-violet-900/60 dark:bg-zinc-950 dark:text-white">
                            @foreach([1200, 2200, 3600, 5600, 8000] as $tokenOption)
                                <option value="{{ $tokenOption }}">{{ number_format($tokenOption) }} tokens</option>
                            @endforeach
                        </select>
                        <flux:error name="maxTokens" />
                    </flux:field>
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
                        <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Incluye tema, instrucción editorial, formato, tamaño y tokens.</p>
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
                                <option value="article">A partir del artículo</option>
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
                        <p class="mt-2 text-xs text-zinc-500 dark:text-zinc-400">Se generará a partir del título del artículo.</p>
                    @endif
                @endif
            </div>
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
                <span wire:loading.remove wire:target="create">Crear post</span>
                <span wire:loading wire:target="create" x-text="`Creando... ${elapsedLabel()} / máx {{ config('starcho_ai.request_timeout', 120) }}s`">Creando...</span>
            </button>
        </x-slot:actions>
    </x-starcho-popup-standar>
</div>
