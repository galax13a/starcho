<div>
    @php
        $isContentTarget = $this->target === 'content';
        $resultHelp = match ($this->target) {
            'excerpt' => 'Revisa el extracto antes de aplicarlo al campo.',
            'seo' => 'Revisa el JSON antes de llenar los campos SEO.',
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
                    <input list="page-ai-models" wire:model="model" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 outline-none transition focus:border-violet-400 focus:ring-2 focus:ring-violet-100 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white dark:focus:ring-violet-900/30">
                    <datalist id="page-ai-models">
                        @foreach($models as $modelName)
                            <option value="{{ $modelName }}"></option>
                        @endforeach
                    </datalist>
                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">El listado cambia según las llaves configuradas en AI.</p>
                    <flux:error name="model" />
                </flux:field>

                @if($isContentTarget)
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
                <span wire:loading wire:target="generate">Generando...</span>
            </button>

            <button
                type="button"
                wire:click="applyResult"
                @disabled(! filled($result))
                class="group relative inline-flex h-10 items-center justify-center gap-2 overflow-hidden rounded-lg border border-slate-950 bg-slate-950 px-4 text-sm font-semibold text-white shadow-[0_1px_1px_rgba(0,0,0,.12),0_8px_20px_rgba(15,23,42,.16)] transition hover:-translate-y-0.5 hover:bg-black disabled:pointer-events-none disabled:translate-y-0 disabled:border-zinc-300 disabled:bg-zinc-200 disabled:text-zinc-500 disabled:shadow-none dark:border-white/10 dark:bg-white dark:text-zinc-950 dark:hover:bg-zinc-100"
            >
                <span class="absolute inset-x-0 top-0 h-px bg-white/40 dark:bg-zinc-950/10"></span>
                <i class="fas fa-check text-xs opacity-90"></i>
                Aplicar
            </button>
        </x-slot:actions>
    </x-starcho-popup-standar>
</div>
