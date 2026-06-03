<div>
    @if($open && $this->media)
        @php
            $media = $this->media;
            $isFavorite = $media->favorites->contains('user_id', auth()->id());
            $averageRating = $media->ratings->isNotEmpty() ? round($media->ratings->avg('rating'), 1) : null;
            $mediaUrl = $media->public_url;
            $variantsEnabled = \App\Models\StorageSetting::singleton()->imageVariantsEnabled();
            $viewerImageUrl = (! $variantsEnabled || $variantSize === 'original') ? $mediaUrl : $media->variantUrl($variantSize);
            $downloadVariants = collect($media->variants ?? [])->filter(fn ($variant) => filled($variant['path'] ?? null));
        @endphp

        <div
            class="fixed inset-0 z-[80] flex items-center justify-center overflow-y-auto bg-black/85 p-2 backdrop-blur-md sm:p-4"
            x-data="{
                copyUrl(url) {
                    navigator.clipboard.writeText(url).then(() => window.Starcho?.notify?.('success', 'URL copiada.'));
                }
            }"
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

            <div wire:key="admin-media-viewer-{{ $media->id }}" class="group relative flex w-full max-w-[800px] flex-col overflow-hidden rounded-xl border border-white/10 bg-zinc-950 shadow-2xl" style="width: min(800px, calc(100vw - 1rem)); max-height: min(640px, calc(100vh - 1rem)); animation: media-viewer-in 190ms ease-out;">
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

                    <button type="button" wire:click="closeViewer" class="grid size-9 shrink-0 place-items-center rounded-lg bg-white/10 text-white transition hover:bg-white/20" title="Cerrar">
                        <flux:icon.x-mark class="size-4" />
                    </button>
                </div>

                <div class="relative flex min-h-[220px] flex-1 items-center justify-center overflow-hidden bg-black" style="max-height: min(430px, calc(100vh - 13rem));">
                    @if($media->isImage())
                        <img src="{{ $viewerImageUrl }}" alt="{{ $media->alt ?? $media->name }}" class="h-auto w-auto object-contain transition duration-300 ease-out" style="max-width: 100%; max-height: min(430px, calc(100vh - 13rem));">
                    @elseif($media->isVideo())
                        <video src="{{ $mediaUrl }}" controls autoplay class="h-auto w-auto object-contain transition duration-300 ease-out" style="max-width: 100%; max-height: min(430px, calc(100vh - 13rem));"></video>
                    @endif
                </div>

                <div class="shrink-0 border-t border-white/10 bg-zinc-950/95 p-3">
                    <div class="flex flex-wrap items-center gap-2">
                        @if(count($mediaIds) > 1)
                            <button type="button" wire:click="previous" class="grid size-10 place-items-center rounded-lg bg-white/10 text-white transition hover:bg-white hover:text-zinc-950" title="Anterior">
                                <flux:icon.chevron-left class="size-5" />
                            </button>

                            <button type="button" wire:click="next" class="grid size-10 place-items-center rounded-lg bg-white/10 text-white transition hover:bg-white hover:text-zinc-950" title="Siguiente">
                                <flux:icon.chevron-right class="size-5" />
                            </button>
                        @endif

                        @if($variantsEnabled && $media->isImage())
                            <div class="inline-flex items-center gap-1 rounded-lg bg-white/10 p-1">
                                @foreach(\App\Models\StorageSetting::singleton()->imageVariantSizes() as $size)
                                    <button type="button" wire:click="setVariantSize('{{ $size }}')" class="h-8 rounded-md px-2 text-[11px] font-semibold transition {{ $variantSize === (string) $size ? 'bg-white text-zinc-950' : 'text-white hover:bg-white/10' }}" title="Ver {{ $size }}px">
                                        {{ $size }}
                                    </button>
                                @endforeach
                                <button type="button" wire:click="setVariantSize('original')" class="h-8 rounded-md px-2 text-[11px] font-semibold transition {{ $variantSize === 'original' ? 'bg-white text-zinc-950' : 'text-white hover:bg-white/10' }}" title="Ver original">
                                    Original
                                </button>
                            </div>
                        @endif

                        <div class="inline-flex flex-wrap items-center gap-1 rounded-lg bg-white/10 p-1">
                            <a href="{{ route('admin.media.download', $media) }}" class="inline-flex h-8 items-center gap-1.5 rounded-md px-2 text-[11px] font-semibold text-white transition hover:bg-white hover:text-zinc-950" title="Descargar original">
                                <flux:icon.arrow-down-tray class="size-4" />
                                Original
                            </a>
                            @if($variantsEnabled && $media->isImage() && $downloadVariants->isNotEmpty())
                                @foreach($downloadVariants as $size => $variant)
                                    <a href="{{ route('admin.media.download', ['media' => $media, 'variant' => $size]) }}" class="inline-flex h-8 items-center gap-1.5 rounded-md px-2 text-[11px] font-semibold text-white transition hover:bg-white hover:text-zinc-950" title="Descargar copia {{ $size }}px · {{ isset($variant['size']) ? number_format(((int) $variant['size']) / 1024, 1) . ' KB' : 'copia generada' }}">
                                        {{ $size }}px
                                    </a>
                                @endforeach
                            @endif
                        </div>

                        <a href="{{ $mediaUrl }}" target="_blank" rel="noopener" class="grid size-10 place-items-center rounded-lg bg-white/10 text-white transition hover:bg-white hover:text-zinc-950" title="Abrir URL">
                            <flux:icon.arrow-top-right-on-square class="size-5" />
                        </a>

                        <button type="button" x-on:click="copyUrl(@js($mediaUrl))" class="grid size-10 place-items-center rounded-lg bg-white/10 text-white transition hover:bg-white hover:text-zinc-950" title="Copiar URL">
                            <flux:icon.clipboard-document class="size-5" />
                        </button>

                        <button type="button" wire:click="toggleFavorite" class="grid size-10 place-items-center rounded-lg bg-white/10 transition hover:bg-white hover:text-zinc-950 {{ $isFavorite ? 'text-rose-300' : 'text-white' }}" title="Favorito">
                            <flux:icon.heart class="size-5 {{ $isFavorite ? 'fill-current' : '' }}" />
                        </button>

                        <form wire:submit="saveRating" class="inline-flex items-center gap-1 rounded-lg bg-white/10 p-1">
                            <label class="sr-only" for="viewer-rating">Calificación</label>
                            <flux:icon.star class="mx-1 size-4 text-amber-300" />
                            <select id="viewer-rating" wire:model="rating" class="h-8 rounded-md border-0 bg-zinc-900 px-2 text-xs text-white focus:ring-2 focus:ring-amber-300">
                                @for($rate = 1; $rate <= 10; $rate++)
                                    <option value="{{ $rate }}">{{ $rate }}</option>
                                @endfor
                            </select>
                            <button type="submit" class="grid size-8 place-items-center rounded-md text-white transition hover:bg-white hover:text-zinc-950" title="Calificar" wire:loading.attr="disabled" wire:target="saveRating">
                                <flux:icon.check class="size-4" />
                            </button>
                        </form>

                        <form wire:submit="addComment" class="flex min-w-[220px] flex-1 items-center gap-1 rounded-lg bg-white/10 p-1">
                            <label class="sr-only" for="viewer-comment">Comentario</label>
                            <input id="viewer-comment" wire:model="comment" type="text" required maxlength="1000" placeholder="Comentar..." class="h-8 min-w-0 flex-1 rounded-md border-0 bg-zinc-900 px-2 text-xs text-white placeholder:text-zinc-500 focus:ring-2 focus:ring-violet-300">
                            <button type="submit" class="grid size-8 place-items-center rounded-md text-white transition hover:bg-white hover:text-zinc-950" title="Comentar" wire:loading.attr="disabled" wire:target="addComment">
                                <flux:icon.paper-airplane class="size-4" />
                            </button>
                        </form>

                        <button type="button" wire:click="openComments" class="grid size-10 place-items-center rounded-lg bg-white/10 text-white transition hover:bg-white hover:text-zinc-950" title="Ver comentarios">
                            <flux:icon.chat-bubble-left-right class="size-5" />
                        </button>

                        <button type="button" wire:click="confirmDelete" class="grid size-10 place-items-center rounded-lg bg-rose-600/90 text-white transition hover:bg-rose-500" title="Eliminar">
                            <flux:icon.trash class="size-5" />
                        </button>
                    </div>

                    <div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-[11px] text-zinc-400">
                        <span class="inline-flex items-center gap-1">
                            <flux:icon.star class="size-3.5 text-amber-300" />
                            {{ $averageRating ? $averageRating . '/10' : 'Sin calificar' }}
                        </span>
                        <button type="button" wire:click="openComments" class="inline-flex items-center gap-1 rounded-md px-1 transition hover:bg-white/10 hover:text-white">
                            <flux:icon.chat-bubble-left class="size-3.5 text-violet-300" />
                            {{ $media->comments->count() }} comentario(s)
                        </button>
                    </div>

                    @error('comment')
                        <p class="mt-2 text-xs text-rose-300">{{ $message }}</p>
                    @enderror
                    @error('rating')
                        <p class="mt-2 text-xs text-rose-300">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            @if($commentsOpen)
                <div class="fixed inset-0 z-[90] flex items-center justify-center bg-black/55 p-3 backdrop-blur-sm" style="animation: media-backdrop-in 120ms ease-out;" wire:click.self="closeComments">
                    <div class="flex max-h-[min(560px,calc(100vh-2rem))] w-full max-w-lg flex-col overflow-hidden rounded-xl border border-zinc-700 bg-zinc-950 text-zinc-100 shadow-2xl shadow-black/50" style="animation: media-viewer-in 160ms ease-out;">
                        <div class="flex items-center justify-between gap-3 border-b border-zinc-800 bg-zinc-950 px-4 py-3">
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-white">Comentarios</p>
                                <p class="truncate text-xs text-zinc-400">{{ $media->name }}</p>
                            </div>

                            <button type="button" wire:click="closeComments" class="grid size-9 shrink-0 place-items-center rounded-full bg-white/10 text-white transition hover:bg-white hover:text-zinc-950" title="Cerrar comentarios">
                                <flux:icon.x-mark class="size-5" />
                            </button>
                        </div>

                        <div class="flex-1 space-y-2 overflow-y-auto bg-zinc-950 p-4 text-zinc-100">
                            @forelse($media->comments as $item)
                                <div class="rounded-lg border border-zinc-700 bg-zinc-900 p-3 text-zinc-100 shadow-sm">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <p class="truncate text-sm font-semibold text-white">{{ $item->user?->name ?? 'Admin' }}</p>
                                            <p class="mt-0.5 text-xs text-zinc-400">{{ $item->created_at?->format('d/m/Y H:i') }}</p>
                                        </div>

                                        <button type="button" wire:click="confirmDeleteComment({{ $item->id }})" class="grid size-9 shrink-0 place-items-center rounded-lg bg-rose-500/10 text-rose-200 transition hover:bg-rose-500 hover:text-white" title="Eliminar comentario">
                                            <flux:icon.trash class="size-4" />
                                        </button>
                                    </div>

                                    <p class="mt-3 whitespace-pre-line text-sm leading-6 text-zinc-100">{{ $item->body }}</p>
                                </div>
                            @empty
                                <div class="rounded-lg border border-dashed border-zinc-700 bg-zinc-900 p-6 text-center text-zinc-100">
                                    <flux:icon.chat-bubble-left-right class="mx-auto size-8 text-zinc-600" />
                                    <p class="mt-2 text-sm font-semibold text-white">Sin comentarios</p>
                                    <p class="mt-1 text-xs text-zinc-500">Agrega el primer comentario desde el visor.</p>
                                </div>
                            @endforelse
                        </div>

                        <form wire:submit="addComment" class="flex items-center gap-2 border-t border-white/10 bg-zinc-900/80 p-3">
                            <label class="sr-only" for="viewer-comment-popup">Nuevo comentario</label>
                            <input id="viewer-comment-popup" wire:model="comment" type="text" required maxlength="1000" placeholder="Nuevo comentario..." class="h-10 min-w-0 flex-1 rounded-lg border border-white/10 bg-black/40 px-3 text-sm text-white placeholder:text-zinc-500 focus:border-violet-300 focus:ring-2 focus:ring-violet-300/30">
                            <button type="submit" class="h-10 shrink-0 rounded-lg bg-white px-4 text-xs font-semibold text-zinc-950 transition hover:bg-zinc-200" wire:loading.attr="disabled" wire:target="addComment">
                                Enviar
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    @endif
</div>
