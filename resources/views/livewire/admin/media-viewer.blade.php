<div>
    @if($open && $this->media)
        @php
            $media = $this->media;
            $isFavorite = $media->favorites->contains('user_id', auth()->id());
            $averageRating = $media->ratings->isNotEmpty() ? round($media->ratings->avg('rating'), 1) : null;
        @endphp

        <div
            class="fixed inset-0 z-[80] flex items-center justify-center overflow-y-auto bg-black/85 p-2 backdrop-blur-md sm:p-4"
            x-data
            style="animation: media-backdrop-in 160ms ease-out;"
            x-on:keydown.escape.window="$wire.closeViewer()"
            x-on:keydown.arrow-left.window="$wire.previous()"
            x-on:keydown.arrow-right.window="$wire.next()"
            wire:click.self="closeViewer"
        >
            <style>
                @keyframes media-backdrop-in {
                    from { opacity: 0; }
                    to { opacity: 1; }
                }

                @keyframes media-viewer-in {
                    from { opacity: 0; transform: scale(.96) translateY(10px); }
                    to { opacity: 1; transform: scale(1) translateY(0); }
                }
            </style>

            <div class="group relative flex w-full max-w-[800px] flex-col overflow-hidden rounded-xl border border-white/10 bg-zinc-950 shadow-2xl" style="width: min(800px, calc(100vw - 1rem)); max-height: min(640px, calc(100vh - 1rem)); animation: media-viewer-in 190ms ease-out;">
                <div class="flex min-h-14 items-center justify-between gap-3 border-b border-white/10 bg-zinc-950/95 px-3 py-2.5 sm:px-4">
                    <div class="min-w-0">
                        <p class="truncate text-sm font-semibold text-white">{{ $media->name }}</p>
                        <p class="mt-0.5 text-xs text-zinc-400">
                            {{ $this->currentIndex + 1 }}/{{ count($mediaIds) }}
                            <span class="mx-1">·</span>
                            {{ $media->sizeLabel() }}
                            <span class="mx-1">·</span>
                            {{ $media->width && $media->height ? $media->width . 'x' . $media->height : strtoupper($media->fileType()) }}
                        </p>
                    </div>

                    <button type="button" wire:click="closeViewer" class="inline-flex size-9 items-center justify-center rounded-lg bg-white/10 text-white transition hover:bg-white/20" title="Cerrar">
                        <i class="fas fa-xmark text-xs"></i>
                    </button>
                </div>

                <div class="relative flex min-h-[220px] flex-1 items-center justify-center overflow-hidden bg-black" style="max-height: min(430px, calc(100vh - 13rem));">
                    <div class="pointer-events-none absolute inset-y-0 left-0 z-20 flex items-center px-2 sm:px-3">
                        <button type="button" wire:click="previous" class="pointer-events-auto grid size-11 place-items-center rounded-full bg-black/65 text-white opacity-90 ring-1 ring-white/15 backdrop-blur transition hover:scale-110 hover:bg-white hover:text-zinc-950 group-hover:opacity-100" title="Anterior">
                            <i class="fas fa-chevron-left block translate-x-[-1px] text-sm leading-none"></i>
                        </button>
                    </div>

                    <div class="pointer-events-none absolute inset-y-0 right-0 z-20 flex items-center px-2 sm:px-3">
                        <button type="button" wire:click="next" class="pointer-events-auto grid size-11 place-items-center rounded-full bg-black/65 text-white opacity-90 ring-1 ring-white/15 backdrop-blur transition hover:scale-110 hover:bg-white hover:text-zinc-950 group-hover:opacity-100" title="Siguiente">
                            <i class="fas fa-chevron-right block translate-x-[1px] text-sm leading-none"></i>
                        </button>
                    </div>

                    @if($media->isImage())
                        <img src="{{ $media->public_url }}" alt="{{ $media->alt ?? $media->name }}" class="h-auto w-auto object-contain transition duration-300 ease-out" style="max-width: 100%; max-height: min(430px, calc(100vh - 13rem));">
                    @elseif($media->isVideo())
                        <video src="{{ $media->public_url }}" controls autoplay class="h-auto w-auto object-contain transition duration-300 ease-out" style="max-width: 100%; max-height: min(430px, calc(100vh - 13rem));"></video>
                    @endif
                </div>

                <div class="shrink-0 border-t border-white/10 bg-zinc-950/95 p-3">
                    <div class="flex flex-wrap items-center gap-2">
                        <a href="{{ route('admin.media.download', $media) }}" class="inline-flex size-9 items-center justify-center rounded-lg bg-white/10 text-white transition hover:bg-white hover:text-zinc-950" title="Descargar">
                            <i class="fas fa-download text-xs"></i>
                        </a>

                        <button type="button" wire:click="toggleFavorite" class="inline-flex size-9 items-center justify-center rounded-lg bg-white/10 transition hover:bg-white hover:text-zinc-950 {{ $isFavorite ? 'text-rose-300' : 'text-white' }}" title="Favorito">
                            <i class="fas fa-heart text-xs"></i>
                        </button>

                        <form wire:submit="saveRating" class="inline-flex items-center gap-1 rounded-lg bg-white/10 p-1">
                            <label class="sr-only" for="viewer-rating">Calificación</label>
                            <i class="fas fa-star px-1 text-xs text-amber-300"></i>
                            <select id="viewer-rating" wire:model="rating" class="h-7 rounded-md border-0 bg-zinc-900 px-1.5 text-xs text-white focus:ring-2 focus:ring-amber-300">
                                @for($rate = 1; $rate <= 10; $rate++)
                                    <option value="{{ $rate }}">{{ $rate }}</option>
                                @endfor
                            </select>
                            <button type="submit" class="inline-flex size-7 items-center justify-center rounded-md text-white transition hover:bg-white hover:text-zinc-950" title="Calificar" wire:loading.attr="disabled" wire:target="saveRating">
                                <i class="fas fa-check text-[10px]"></i>
                            </button>
                        </form>

                        <form wire:submit="addComment" class="flex min-w-[220px] flex-1 items-center gap-1 rounded-lg bg-white/10 p-1">
                            <label class="sr-only" for="viewer-comment">Comentario</label>
                            <input id="viewer-comment" wire:model="comment" type="text" required maxlength="1000" placeholder="Comentar..." class="h-7 min-w-0 flex-1 rounded-md border-0 bg-zinc-900 px-2 text-xs text-white placeholder:text-zinc-500 focus:ring-2 focus:ring-violet-300">
                            <button type="submit" class="inline-flex size-7 items-center justify-center rounded-md text-white transition hover:bg-white hover:text-zinc-950" title="Comentar" wire:loading.attr="disabled" wire:target="addComment">
                                <i class="fas fa-comment text-[10px]"></i>
                            </button>
                        </form>

                        <button type="button" wire:click="confirmDelete" class="inline-flex size-9 items-center justify-center rounded-lg bg-rose-600/90 text-white transition hover:bg-rose-500" title="Eliminar">
                            <i class="fas fa-trash text-xs"></i>
                        </button>
                    </div>

                    <div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-[11px] text-zinc-400">
                        <span>
                            <i class="fas fa-star text-amber-300"></i>
                            {{ $averageRating ? $averageRating . '/10' : 'Sin calificar' }}
                        </span>
                        <span>
                            <i class="fas fa-comment text-violet-300"></i>
                            {{ $media->comments->count() }} comentario(s)
                        </span>
                        @if($media->comments->isNotEmpty())
                            <span class="truncate text-zinc-500">{{ $media->comments->first()->user?->name ?? 'Admin' }}: {{ $media->comments->first()->body }}</span>
                        @endif
                    </div>

                    @error('comment')
                        <p class="mt-2 text-xs text-rose-300">{{ $message }}</p>
                    @enderror
                    @error('rating')
                        <p class="mt-2 text-xs text-rose-300">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>
    @endif
</div>
