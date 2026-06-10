<div>
    @php
        $isContentTarget = in_array($this->target, ['content', 'inspiration', 'memory_regenerate', 'translate_locale'], true);
        $isAuditTarget = $this->target === 'audit';
        $resultHelp = match ($this->target) {
            'excerpt' => 'Revisa el extracto antes de aplicarlo al campo.',
            'seo' => 'Revisa el JSON antes de llenar los campos SEO.',
            'inspiration' => 'Revisa las ideas; si te sirven puedes agregarlas al final del artículo.',
            'audit' => 'Resultado informativo: no se aplica al editor.',
            'memory_regenerate' => 'Regenera una versión completa basada en las memorias seleccionadas.',
            'translate_locale' => 'Revisa la versión recreada para este idioma antes de aplicarla.',
            default => 'Revisa el texto antes de aplicarlo.',
        };
    @endphp

    <x-starcho-popup-standar
        name="modal-page-ai-assistant"
        width="md:w-[920px]"
        submit-action="generate"
        title="Asistente AI de {{ $targetLabel }}"
        subtitle="Genera {{ $targetLabel }} para el idioma activo y aplícalo antes de publicar."
        save-label="Generar contenido"
        saving-label="Generando..."
        loading-target="generate"
        form-class="space-y-5"
    >
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-[300px_1fr]">
            <div class="space-y-4">
                @unless($settings->enabled && $settings->hasAnyProviderKey())
                    <div class="rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-700 dark:border-amber-700/50 dark:bg-amber-900/20 dark:text-amber-200">
                        Activa AI y guarda al menos una llave en <a href="{{ route('admin.site.index', ['tab' => 'ai']) }}" class="font-semibold underline">admin/site > AI</a>.
                    </div>
                @endunless

                <flux:field>
                    <flux:label>Idioma activo</flux:label>
                    <div class="inline-flex items-center gap-2 rounded-lg border border-zinc-200 bg-zinc-50 px-3 py-2 text-sm font-semibold uppercase text-violet-700 dark:border-zinc-700 dark:bg-zinc-800 dark:text-violet-300">
                        <i class="fas fa-language text-xs"></i>
                        {{ $locale }}
                    </div>
                </flux:field>

                @if($this->target === 'translate_locale')
                    <flux:field>
                        <flux:label>Idioma base</flux:label>
                        <div class="inline-flex items-center gap-2 rounded-lg border border-sky-200 bg-sky-50 px-3 py-2 text-sm font-semibold uppercase text-sky-700 dark:border-sky-900/60 dark:bg-sky-950/30 dark:text-sky-300">
                            <i class="fas fa-arrow-right-arrow-left text-xs"></i>
                            {{ $sourceLocale }}
                        </div>
                        <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">El editor ya envió el contenido de este idioma como referencia.</p>
                    </flux:field>
                @endif

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
                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">El listado cambia según las llaves configuradas en AI.</p>
                    <flux:error name="model" />
                </flux:field>

                @if($isContentTarget)
                    <flux:field>
                        <flux:label>Formato de salida</flux:label>
                        <div class="grid gap-2">
                            <label class="cursor-pointer rounded-xl border p-3 transition {{ $outputFormat === 'editorjs' ? 'border-violet-400 bg-violet-50 text-violet-800 dark:bg-violet-900/20 dark:text-violet-100' : 'border-zinc-200 text-zinc-600 hover:border-zinc-300 dark:border-zinc-700 dark:text-zinc-300' }}">
                                <input type="radio" wire:model.live="outputFormat" value="editorjs" class="sr-only">
                                <span class="block text-sm font-semibold">Editor.js estructurado</span>
                                <span class="mt-1 block text-xs opacity-75">Bloques nativos para editar texto y listas.</span>
                            </label>
                            <label class="cursor-pointer rounded-xl border p-3 transition {{ $outputFormat === 'html' ? 'border-violet-400 bg-violet-50 text-violet-800 dark:bg-violet-900/20 dark:text-violet-100' : 'border-zinc-200 text-zinc-600 hover:border-zinc-300 dark:border-zinc-700 dark:text-zinc-300' }}">
                                <input type="radio" wire:model.live="outputFormat" value="html" class="sr-only">
                                <span class="block text-sm font-semibold">HTML + Tailwind</span>
                                <span class="mt-1 block text-xs opacity-75">Renderiza en el bloque Starcho HTML y permite editar markup.</span>
                            </label>
                        </div>
                        <flux:error name="outputFormat" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Modo al aplicar</flux:label>
                        <div class="grid grid-cols-2 gap-2">
                            <label class="cursor-pointer rounded-lg border px-3 py-2 text-center text-sm transition {{ $mode === 'replace' ? 'border-violet-400 bg-violet-50 text-violet-700 dark:bg-violet-900/20 dark:text-violet-200' : 'border-zinc-200 text-zinc-600 dark:border-zinc-700 dark:text-zinc-300' }}">
                                <input type="radio" wire:model.live="mode" value="replace" class="sr-only">
                                Reemplazar
                            </label>
                            <label class="cursor-pointer rounded-lg border px-3 py-2 text-center text-sm transition {{ $mode === 'append' ? 'border-violet-400 bg-violet-50 text-violet-700 dark:bg-violet-900/20 dark:text-violet-200' : 'border-zinc-200 text-zinc-600 dark:border-zinc-700 dark:text-zinc-300' }}">
                                <input type="radio" wire:model.live="mode" value="append" class="sr-only">
                                Agregar
                            </label>
                        </div>
                        <flux:error name="mode" />
                    </flux:field>
                @endif

                <flux:field>
                    <flux:label>Prompt</flux:label>
                    <textarea wire:model="prompt" rows="8" maxlength="4000" class="w-full resize-none rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 outline-none transition placeholder:text-zinc-400 focus:border-violet-400 focus:ring-2 focus:ring-violet-100 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white dark:placeholder:text-zinc-600 dark:focus:ring-violet-900/30" placeholder="Ej: mejora este contenido para una landing, agrega beneficios y conserva el tono actual."></textarea>
                    <flux:error name="prompt" />
                </flux:field>

                @if($this->target === 'memory_regenerate')
                    <div class="rounded-xl border border-sky-200 bg-sky-50/70 p-3 dark:border-sky-900/60 dark:bg-sky-950/20">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-widest text-sky-700 dark:text-sky-200">Memory del artículo</p>
                                <p class="mt-1 text-xs leading-5 text-sky-700/80 dark:text-sky-200/70">Selecciona qué borradores y resultados AI deben alimentar la nueva versión.</p>
                            </div>
                            <span class="rounded-full bg-white px-2 py-1 text-[11px] font-bold text-sky-700 ring-1 ring-sky-100 dark:bg-zinc-950 dark:text-sky-200 dark:ring-sky-900/60">
                                {{ count($selectedMemoryIds) }} usadas
                            </span>
                        </div>

                        <div class="mt-3 max-h-48 space-y-2 overflow-y-auto pr-1">
                            @forelse($memories as $memory)
                                <label class="flex cursor-pointer items-start gap-3 rounded-lg border bg-white p-3 text-left transition {{ in_array($memory->id, $selectedMemoryIds, true) ? 'border-sky-400 ring-2 ring-sky-100 dark:ring-sky-900/30' : 'border-sky-100 hover:border-sky-300 dark:border-sky-900/50' }} dark:bg-zinc-950">
                                    <input type="checkbox" wire:model.live="selectedMemoryIds" value="{{ $memory->id }}" class="mt-1 rounded border-zinc-300 text-sky-600 focus:ring-sky-500">
                                    <span class="min-w-0">
                                        <span class="block truncate text-sm font-semibold text-zinc-900 dark:text-white">{{ $memory->title }}</span>
                                        <span class="mt-1 block text-xs text-zinc-500 dark:text-zinc-400">
                                            {{ $memory->status }} · {{ $memory->source }} · {{ $memory->created_at?->format('d/m/Y H:i') }}
                                        </span>
                                        <span class="mt-2 block line-clamp-2 text-xs leading-5 text-zinc-600 dark:text-zinc-300">{{ strip_tags($memory->body) }}</span>
                                    </span>
                                </label>
                            @empty
                                <div class="rounded-lg border border-dashed border-sky-200 bg-white p-4 text-center text-xs text-sky-700 dark:border-sky-900/60 dark:bg-zinc-950 dark:text-sky-200">
                                    Este artículo todavía no tiene memorias. Genera una inspiración o contenido AI primero.
                                </div>
                            @endforelse
                        </div>
                    </div>
                @endif

                @if($this->target === 'inspiration')
                    <div class="rounded-xl border border-violet-200 bg-violet-50/70 p-3 dark:border-violet-900/60 dark:bg-violet-950/20">
                        <p class="text-xs font-semibold uppercase tracking-widest text-violet-700 dark:text-violet-200">Inspiraciones rápidas</p>
                        <div class="mt-2 grid gap-1.5">
                            @foreach($inspirationPrompts as $quickPrompt)
                                <button type="button" wire:click="$set('prompt', @js($quickPrompt))" class="rounded-lg bg-white px-3 py-2 text-left text-xs font-medium text-zinc-700 ring-1 ring-violet-100 transition hover:text-violet-700 dark:bg-zinc-950 dark:text-zinc-200 dark:ring-violet-900/50">
                                    {{ $quickPrompt }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <div class="min-w-0 space-y-3">
                @if($errorMessage)
                    <div class="rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-700 dark:border-rose-700/50 dark:bg-rose-900/20 dark:text-rose-200">
                        {{ $errorMessage }}
                    </div>
                @endif

                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold text-zinc-800 dark:text-zinc-100">Resultado</p>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $resultHelp }}</p>
                    </div>
                    @if(filled($result))
                        <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">
                            Listo para aplicar
                        </span>
                    @endif
                </div>

                <div class="max-h-[360px] min-h-[320px] overflow-y-auto rounded-xl border border-zinc-200 bg-zinc-50 p-4 text-sm leading-7 text-zinc-800 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-100">
                    @if(filled($result))
                        <div class="whitespace-pre-wrap">{{ $result }}</div>
                    @else
                        <div class="flex h-[280px] items-center justify-center text-center text-sm text-zinc-400">
                            Escribe un prompt y genera una propuesta para este idioma.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <x-slot:actions>
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
                wire:target="generate"
            >
                <i wire:loading.remove wire:target="generate" class="fas fa-wand-magic-sparkles text-xs"></i>
                <svg wire:loading wire:target="generate" class="size-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span wire:loading.remove wire:target="generate">Generar</span>
                <span wire:loading wire:target="generate" x-text="`Generando... ${elapsedLabel()} / máx {{ config('starcho_ai.request_timeout', 120) }}s`">Generando...</span>
            </button>

            <button
                type="button"
                wire:click="applyResult"
                @disabled(! filled($result) || $isAuditTarget)
                class="group relative inline-flex h-10 items-center justify-center gap-2 overflow-hidden rounded-lg border border-slate-950 bg-slate-950 px-4 text-sm font-semibold text-white shadow-[0_1px_1px_rgba(0,0,0,.12),0_8px_20px_rgba(15,23,42,.16)] transition hover:-translate-y-0.5 hover:bg-black disabled:pointer-events-none disabled:translate-y-0 disabled:border-zinc-300 disabled:bg-zinc-200 disabled:text-zinc-500 disabled:shadow-none dark:border-white/10 dark:bg-white dark:text-zinc-950 dark:hover:bg-zinc-100"
            >
                <span class="absolute inset-x-0 top-0 h-px bg-white/40 dark:bg-zinc-950/10"></span>
                <i class="fas fa-check text-xs opacity-90"></i>
                Aplicar
            </button>
        </x-slot:actions>
    </x-starcho-popup-standar>
</div>
