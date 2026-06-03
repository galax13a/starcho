<div>
    <x-starcho-popup-standar
        name="modal-post-insights"
        width="md:w-[1040px]"
        title="Stats del contenido"
        subtitle="Historial AI, tokens, prompt enviado y comentarios."
    >
        @if($this->post)
            <div class="mt-5 space-y-4">
                <div class="flex flex-wrap items-start justify-between gap-3 rounded-xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-800 dark:bg-zinc-900/60">
                    <div class="min-w-0">
                        <p class="truncate text-base font-semibold text-zinc-950 dark:text-white">{{ $this->post->title }}</p>
                        <p class="mt-1 text-xs text-zinc-500">
                            {{ ucfirst($this->post->type) }} #{{ $this->post->id }} · {{ $this->post->status }} · {{ $this->post->created_at?->format('d/m/Y H:i') }}
                        </p>
                    </div>
                    <a href="{{ $this->post->public_url }}" target="_blank" class="inline-flex h-9 items-center gap-2 rounded-lg border border-zinc-200 bg-white px-3 text-xs font-semibold text-zinc-700 transition hover:bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-200">
                        <i class="fas fa-arrow-up-right-from-square text-[10px]"></i>
                        Ver página
                    </a>
                </div>

                <div class="inline-flex rounded-xl border border-zinc-200 bg-white p-1 text-sm dark:border-zinc-800 dark:bg-zinc-950">
                    @foreach(['stats' => 'Stats', 'ai' => 'AI', 'memory' => 'Memory', 'comments' => 'Comentarios'] as $key => $label)
                        <button type="button" wire:click="$set('tab', '{{ $key }}')" class="rounded-lg px-3 py-1.5 font-semibold transition {{ $tab === $key ? 'bg-zinc-950 text-white dark:bg-white dark:text-zinc-950' : 'text-zinc-500 hover:text-zinc-900 dark:hover:text-white' }}">
                            {{ $label }}
                        </button>
                    @endforeach
                </div>

                @if($tab === 'stats')
                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-6">
                        @foreach([
                            ['label' => 'Palabras', 'value' => number_format($stats['words']), 'icon' => 'fa-align-left'],
                            ['label' => 'Lectura', 'value' => $stats['read_minutes'].' min', 'icon' => 'fa-clock'],
                            ['label' => 'Visitas', 'value' => number_format($stats['views']), 'icon' => 'fa-eye'],
                            ['label' => 'AI runs', 'value' => number_format($stats['ai_runs']), 'icon' => 'fa-wand-magic-sparkles'],
                            ['label' => 'Memories', 'value' => number_format($stats['memories']), 'icon' => 'fa-brain'],
                            ['label' => 'Rating AI', 'value' => $stats['avg_rating'] ?: '—', 'icon' => 'fa-star'],
                        ] as $card)
                            <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-950">
                                <i class="fas {{ $card['icon'] }} text-sm text-violet-500"></i>
                                <p class="mt-3 text-xs font-semibold uppercase tracking-widest text-zinc-400">{{ $card['label'] }}</p>
                                <p class="mt-1 text-xl font-bold text-zinc-950 dark:text-white">{{ $card['value'] }}</p>
                            </div>
                        @endforeach
                    </div>
                @elseif($tab === 'ai')
                    <div class="grid gap-4 lg:grid-cols-[320px_1fr]">
                        <div class="max-h-[560px] space-y-2 overflow-y-auto pr-1">
                            @forelse($this->post->aiGenerations as $run)
                                <button type="button" wire:click="selectGeneration({{ $run->id }})" class="w-full rounded-xl border p-3 text-left transition {{ $this->selectedGeneration?->id === $run->id ? 'border-violet-300 bg-violet-50 dark:border-violet-800 dark:bg-violet-950/30' : 'border-zinc-200 bg-white hover:bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-950 dark:hover:bg-zinc-900' }}">
                                    <p class="truncate text-sm font-semibold text-zinc-950 dark:text-white">{{ $run->model }}</p>
                                    <p class="mt-1 text-xs text-zinc-500">{{ $run->provider }} · {{ $run->action }} · {{ $run->created_at?->format('d/m/Y H:i') }}</p>
                                    <p class="mt-2 text-xs font-semibold text-violet-600 dark:text-violet-300">{{ number_format($run->total_tokens) }} tokens</p>
                                </button>
                            @empty
                                <div class="rounded-xl border border-dashed border-zinc-300 p-6 text-center text-sm text-zinc-500 dark:border-zinc-700">
                                    Aún no hay generaciones AI guardadas.
                                </div>
                            @endforelse
                        </div>

                        @if($this->selectedGeneration)
                            <div class="max-h-[560px] overflow-y-auto rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-950">
                                <div class="grid gap-2 sm:grid-cols-4">
                                    <div><p class="text-[11px] text-zinc-400">Proveedor</p><p class="text-sm font-semibold">{{ $this->selectedGeneration->provider }}</p></div>
                                    <div><p class="text-[11px] text-zinc-400">Modelo</p><p class="text-sm font-semibold">{{ $this->selectedGeneration->model }}</p></div>
                                    <div><p class="text-[11px] text-zinc-400">Tokens</p><p class="text-sm font-semibold">{{ number_format($this->selectedGeneration->total_tokens) }}</p></div>
                                    <div><p class="text-[11px] text-zinc-400">Tiempo</p><p class="text-sm font-semibold">{{ $this->selectedGeneration->duration_ms ? number_format($this->selectedGeneration->duration_ms / 1000, 2).'s' : '—' }}</p></div>
                                </div>

                                <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50/70 p-3 dark:border-amber-900/60 dark:bg-amber-950/20">
                                    <div class="flex flex-wrap items-center justify-between gap-3">
                                        <div>
                                            <p class="text-sm font-semibold text-amber-950 dark:text-amber-100">Evaluar resultado del modelo</p>
                                            <p class="text-xs text-amber-700/80 dark:text-amber-200/70">Califica de 1 a 10 para comparar calidad por proveedor y modelo.</p>
                                        </div>
                                        <div class="flex flex-wrap gap-1">
                                            @for($i = 1; $i <= 10; $i++)
                                                <button type="button" wire:click="rateGeneration({{ $i }})" class="grid size-8 place-items-center rounded-lg text-xs font-bold transition {{ (int) $this->selectedGeneration->rating === $i ? 'bg-amber-500 text-white' : 'bg-white text-amber-700 ring-1 ring-amber-200 hover:bg-amber-100 dark:bg-zinc-950 dark:text-amber-200 dark:ring-amber-900/60' }}">
                                                    {{ $i }}
                                                </button>
                                            @endfor
                                        </div>
                                    </div>
                                    <textarea wire:model="ratingNotes" rows="2" class="mt-3 w-full rounded-lg border border-amber-200 bg-white px-3 py-2 text-xs text-zinc-800 dark:border-amber-900/60 dark:bg-zinc-950 dark:text-zinc-100" placeholder="Notas internas sobre calidad, precisión, tono o utilidad..."></textarea>
                                </div>

                                <div class="mt-4 grid gap-3">
                                    <details open class="rounded-xl border border-zinc-200 p-3 dark:border-zinc-800">
                                        <summary class="cursor-pointer text-sm font-semibold">Prompt completo enviado</summary>
                                        <pre class="mt-3 max-h-64 overflow-auto whitespace-pre-wrap rounded-lg bg-zinc-950 p-3 text-xs leading-5 text-zinc-100">{{ $this->selectedGeneration->prompt_text }}</pre>
                                    </details>
                                    <details class="rounded-xl border border-zinc-200 p-3 dark:border-zinc-800">
                                        <summary class="cursor-pointer text-sm font-semibold">System prompt</summary>
                                        <pre class="mt-3 max-h-64 overflow-auto whitespace-pre-wrap rounded-lg bg-zinc-950 p-3 text-xs leading-5 text-zinc-100">{{ $this->selectedGeneration->system_prompt }}</pre>
                                    </details>
                                    <details class="rounded-xl border border-zinc-200 p-3 dark:border-zinc-800">
                                        <summary class="cursor-pointer text-sm font-semibold">Respuesta AI</summary>
                                        <pre class="mt-3 max-h-64 overflow-auto whitespace-pre-wrap rounded-lg bg-zinc-950 p-3 text-xs leading-5 text-zinc-100">{{ $this->selectedGeneration->response_text }}</pre>
                                    </details>
                                </div>
                            </div>
                        @endif
                    </div>
                @elseif($tab === 'memory')
                    <div class="grid gap-4 lg:grid-cols-[1fr_360px]">
                        <div class="max-h-[560px] space-y-3 overflow-y-auto rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-950">
                            @forelse($this->post->aiMemories as $memory)
                                <article class="rounded-xl border p-4 transition {{ $memory->active ? 'border-sky-200 bg-sky-50/60 dark:border-sky-900/60 dark:bg-sky-950/20' : 'border-zinc-200 bg-zinc-50 opacity-75 dark:border-zinc-800 dark:bg-zinc-900/60' }}">
                                    <div class="flex flex-wrap items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <p class="truncate text-sm font-semibold text-zinc-950 dark:text-white">{{ $memory->title }}</p>
                                                <span class="rounded-full px-2 py-0.5 text-[10px] font-bold uppercase {{ $memory->active ? 'bg-sky-600 text-white' : 'bg-zinc-200 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300' }}">
                                                    {{ $memory->active ? 'Activa' : 'Inactiva' }}
                                                </span>
                                            </div>
                                            <p class="mt-1 text-xs text-zinc-500">
                                                {{ $memory->source }} · {{ $memory->status }} · {{ $memory->created_at?->format('d/m/Y H:i') }}
                                                @if($memory->generation)
                                                    · {{ $memory->generation->provider }}/{{ $memory->generation->model }}
                                                @endif
                                            </p>
                                        </div>
                                        <div class="flex items-center gap-1">
                                            <button type="button" wire:click="toggleMemory({{ $memory->id }})" class="grid size-9 place-items-center rounded-lg border border-zinc-200 bg-white text-zinc-600 transition hover:bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-300" title="{{ $memory->active ? 'Desactivar memory' : 'Activar memory' }}">
                                                <i class="fas {{ $memory->active ? 'fa-toggle-on text-sky-600' : 'fa-toggle-off' }} text-sm"></i>
                                            </button>
                                            <button type="button" wire:click="archiveMemory({{ $memory->id }})" class="grid size-9 place-items-center rounded-lg border border-zinc-200 bg-white text-zinc-600 transition hover:bg-amber-50 hover:text-amber-700 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-300" title="Archivar memory">
                                                <i class="fas fa-box-archive text-sm"></i>
                                            </button>
                                        </div>
                                    </div>

                                    @if($memory->prompt_text)
                                        <details class="mt-3 rounded-lg border border-white/70 bg-white/70 p-3 dark:border-zinc-800 dark:bg-zinc-950/70">
                                            <summary class="cursor-pointer text-xs font-semibold text-zinc-600 dark:text-zinc-300">Prompt usado</summary>
                                            <p class="mt-2 whitespace-pre-wrap text-xs leading-5 text-zinc-600 dark:text-zinc-300">{{ $memory->prompt_text }}</p>
                                        </details>
                                    @endif

                                    <p class="mt-3 line-clamp-5 whitespace-pre-wrap text-sm leading-6 text-zinc-700 dark:text-zinc-200">{{ strip_tags($memory->body) }}</p>
                                </article>
                            @empty
                                <div class="rounded-xl border border-dashed border-zinc-300 p-6 text-center text-sm text-zinc-500 dark:border-zinc-700">
                                    Todavía no hay memory para este artículo.
                                </div>
                            @endforelse
                        </div>

                        <form wire:submit="addManualMemory" class="rounded-xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-800 dark:bg-zinc-900/60">
                            <p class="text-sm font-semibold text-zinc-950 dark:text-white">Agregar memory manual</p>
                            <p class="mt-1 text-xs leading-5 text-zinc-500 dark:text-zinc-400">Guarda notas editoriales, errores a evitar, ideas ganadoras o contexto que quieras reutilizar cuando regeneres el artículo.</p>
                            <input wire:model="memoryTitle" class="mt-3 w-full rounded-xl border border-zinc-200 bg-white px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-950" placeholder="Ej: tono, enfoque o aprendizaje">
                            <flux:error name="memoryTitle" />
                            <textarea wire:model="memoryBody" rows="8" class="mt-3 w-full rounded-xl border border-zinc-200 bg-white px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-950" placeholder="Escribe la memory..."></textarea>
                            <flux:error name="memoryBody" />
                            <flux:button type="submit" variant="primary" icon="plus" class="mt-3 w-full">Guardar memory</flux:button>
                        </form>
                    </div>
                @else
                    <div class="grid gap-4 lg:grid-cols-[1fr_360px]">
                        <div class="max-h-[560px] overflow-y-auto rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-950">
                            @forelse($this->post->rootComments as $comment)
                                @include('livewire.admin.partials.post-comment-node', ['comment' => $comment])
                            @empty
                                <div class="rounded-xl border border-dashed border-zinc-300 p-6 text-center text-sm text-zinc-500 dark:border-zinc-700">
                                    No hay comentarios todavía.
                                </div>
                            @endforelse
                        </div>

                        <form wire:submit="addComment" class="rounded-xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-800 dark:bg-zinc-900/60">
                            <p class="text-sm font-semibold text-zinc-950 dark:text-white">
                                {{ $replyTo ? 'Responder comentario' : 'Nuevo comentario' }}
                            </p>
                            @if($replyTo)
                                <button type="button" wire:click="startReply(null)" class="mt-1 text-xs font-semibold text-violet-600">Cancelar respuesta</button>
                            @endif
                            <textarea wire:model="commentBody" rows="7" class="mt-3 w-full rounded-xl border border-zinc-200 bg-white px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-950" placeholder="Escribe el comentario..."></textarea>
                            <flux:error name="commentBody" />
                            <flux:button type="submit" variant="primary" icon="paper-airplane" class="mt-3 w-full">Guardar comentario</flux:button>
                        </form>
                    </div>
                @endif
            </div>
        @endif
    </x-starcho-popup-standar>
</div>
