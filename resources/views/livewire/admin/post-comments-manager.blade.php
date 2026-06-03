<div class="space-y-5" x-data="{ lightbox: null }">
    @php
        $totalContent = $stats['total'];
        $totalMedia = $stats['media_total'];

        $statusClasses = [
            'approved' => 'bg-emerald-100 text-emerald-700 ring-emerald-200 dark:bg-emerald-950/30 dark:text-emerald-300 dark:ring-emerald-900/50',
            'spam' => 'bg-rose-100 text-rose-700 ring-rose-200 dark:bg-rose-950/30 dark:text-rose-300 dark:ring-rose-900/50',
            'pending' => 'bg-amber-100 text-amber-700 ring-amber-200 dark:bg-amber-950/30 dark:text-amber-300 dark:ring-amber-900/50',
        ];
        $statusLabels = ['approved' => 'Aprobado', 'spam' => 'Spam', 'pending' => 'Pendiente'];
    @endphp

    <div class="grid gap-3 md:grid-cols-3 xl:grid-cols-6">
        @foreach([
            ['label' => 'Contenido', 'value' => $totalContent, 'icon' => 'fa-file-lines', 'color' => '#7c3aed', 'soft' => 'rgba(124,58,237,.10)'],
            ['label' => 'Multimedia', 'value' => $totalMedia, 'icon' => 'fa-photo-film', 'color' => '#0ea5e9', 'soft' => 'rgba(14,165,233,.10)'],
            ['label' => 'Aprobados', 'value' => $stats['approved'] + $stats['media_approved'], 'icon' => 'fa-circle-check', 'color' => '#10b981', 'soft' => 'rgba(16,185,129,.10)'],
            ['label' => 'Pendientes', 'value' => $stats['pending'] + $stats['media_pending'], 'icon' => 'fa-clock', 'color' => '#f59e0b', 'soft' => 'rgba(245,158,11,.12)'],
            ['label' => 'Spam', 'value' => $stats['spam'] + $stats['media_spam'], 'icon' => 'fa-ban', 'color' => '#e11d48', 'soft' => 'rgba(225,29,72,.10)'],
            ['label' => 'Posts/Pages', 'value' => $stats['posts'].'/'.$stats['pages'], 'icon' => 'fa-newspaper', 'color' => '#9333ea', 'soft' => 'rgba(147,51,234,.10)'],
        ] as $card)
            <div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400">{{ $card['label'] }}</p>
                        <p class="mt-1 text-2xl font-black text-zinc-950 dark:text-white">{{ is_numeric($card['value']) ? number_format($card['value']) : $card['value'] }}</p>
                    </div>
                    <span class="inline-flex size-10 shrink-0 items-center justify-center rounded-xl ring-1" style="background:{{ $card['soft'] }}; color:{{ $card['color'] }}; --tw-ring-color:{{ $card['soft'] }};">
                        <i class="fas {{ $card['icon'] }} text-sm leading-none"></i>
                    </span>
                </div>
            </div>
        @endforeach
    </div>

    <div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <div class="flex flex-col gap-4">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-zinc-950 dark:text-white">Centro de comentarios</p>
                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Modera, responde y previsualiza comentarios de posts, páginas, archivos multimedia y álbumes desde un solo dashboard.</p>
                </div>

                @if($this->hasActiveFilters())
                    <button type="button" wire:click="resetFilters" class="inline-flex h-9 shrink-0 items-center gap-1.5 self-start rounded-xl border border-zinc-200 bg-white px-3 text-xs font-semibold text-zinc-600 transition hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-300">
                        <i class="fas fa-rotate-left text-[10px]"></i>Limpiar filtros
                    </button>
                @endif
            </div>

            {{-- Búsqueda --}}
            <div class="relative">
                <i class="fas fa-magnifying-glass pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-zinc-400"></i>
                <input
                    type="search"
                    wire:model.live.debounce.350ms="search"
                    placeholder="Buscar comentario, autor, archivo o página..."
                    class="h-10 w-full rounded-xl border border-zinc-200 bg-zinc-50 pl-9 pr-3 text-sm text-zinc-900 outline-none transition focus:border-violet-300 focus:ring-2 focus:ring-violet-100 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white dark:focus:ring-violet-900/30"
                >
            </div>

            {{-- Filtros --}}
            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                <label class="flex flex-col gap-1">
                    <span class="text-[11px] font-semibold uppercase tracking-wide text-zinc-400">Origen</span>
                    <select wire:model.live="source" class="h-10 rounded-xl border border-zinc-200 bg-zinc-50 px-3 text-sm text-zinc-900 outline-none transition focus:border-violet-300 focus:ring-2 focus:ring-violet-100 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white">
                        <option value="all">Todo</option>
                        <option value="content">Posts y pages</option>
                        <option value="media">Multimedia</option>
                    </select>
                </label>
                <label class="flex flex-col gap-1">
                    <span class="text-[11px] font-semibold uppercase tracking-wide text-zinc-400">Tipo</span>
                    <select wire:model.live="type" @disabled($source === 'media') class="h-10 rounded-xl border border-zinc-200 bg-zinc-50 px-3 text-sm text-zinc-900 outline-none transition focus:border-violet-300 focus:ring-2 focus:ring-violet-100 disabled:cursor-not-allowed disabled:opacity-50 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white">
                        <option value="">Post/Page</option>
                        <option value="post">Solo posts</option>
                        <option value="page">Solo pages</option>
                    </select>
                </label>
                <label class="flex flex-col gap-1">
                    <span class="text-[11px] font-semibold uppercase tracking-wide text-zinc-400">Estado</span>
                    <select wire:model.live="status" class="h-10 rounded-xl border border-zinc-200 bg-zinc-50 px-3 text-sm text-zinc-900 outline-none transition focus:border-violet-300 focus:ring-2 focus:ring-violet-100 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white">
                        <option value="">Todos los estados</option>
                        <option value="approved">Aprobados</option>
                        <option value="pending">Pendientes</option>
                        <option value="spam">Spam</option>
                    </select>
                </label>
                <label class="flex flex-col gap-1">
                    <span class="text-[11px] font-semibold uppercase tracking-wide text-zinc-400">Orden</span>
                    <select wire:model.live="sort" class="h-10 rounded-xl border border-zinc-200 bg-zinc-50 px-3 text-sm text-zinc-900 outline-none transition focus:border-violet-300 focus:ring-2 focus:ring-violet-100 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white">
                        <option value="latest">Más recientes</option>
                        <option value="oldest">Más antiguos</option>
                    </select>
                </label>
                <label class="flex flex-col gap-1">
                    <span class="text-[11px] font-semibold uppercase tracking-wide text-zinc-400">Desde</span>
                    <input type="date" wire:model.live="dateFrom" max="{{ now()->toDateString() }}" class="h-10 rounded-xl border border-zinc-200 bg-zinc-50 px-3 text-sm text-zinc-900 outline-none transition focus:border-violet-300 focus:ring-2 focus:ring-violet-100 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white">
                </label>
                <label class="flex flex-col gap-1">
                    <span class="text-[11px] font-semibold uppercase tracking-wide text-zinc-400">Hasta</span>
                    <input type="date" wire:model.live="dateTo" max="{{ now()->toDateString() }}" class="h-10 rounded-xl border border-zinc-200 bg-zinc-50 px-3 text-sm text-zinc-900 outline-none transition focus:border-violet-300 focus:ring-2 focus:ring-violet-100 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white">
                </label>
                <label class="flex flex-col gap-1">
                    <span class="text-[11px] font-semibold uppercase tracking-wide text-zinc-400">Por página</span>
                    <select wire:model.live="perPage" class="h-10 rounded-xl border border-zinc-200 bg-zinc-50 px-3 text-sm text-zinc-900 outline-none transition focus:border-violet-300 focus:ring-2 focus:ring-violet-100 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </label>
            </div>
        </div>
    </div>

    @if($source !== 'media')
        <section class="space-y-3">
            <div class="flex items-center justify-between">
                <h2 class="text-sm font-black uppercase tracking-widest text-zinc-500 dark:text-zinc-400">Posts y pages</h2>
                <span class="rounded-full bg-violet-50 px-2.5 py-1 text-xs font-bold text-violet-700 dark:bg-violet-950/30 dark:text-violet-300">{{ $comments?->total() ?? 0 }} comentarios</span>
            </div>

            @forelse($comments ?? [] as $comment)
                @php
                    $post = $comment->post;
                    $postType = $post?->type ?? 'post';
                    $editRoute = $post
                        ? ($postType === 'page' ? route('admin.pages.edit', $post) : route('admin.posts.edit', $post))
                        : null;
                    $canReply = $comment->depth < \App\Models\PostComment::MAX_DEPTH;
                @endphp

                <article class="group rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm transition hover:border-violet-200 hover:shadow-lg dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-violet-900/60" @if($comment->parent_id) style="margin-left:{{ min($comment->depth,2) * 22 }}px" @endif>
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="rounded-full px-2.5 py-1 text-[11px] font-bold uppercase ring-1 {{ $statusClasses[$comment->status] ?? $statusClasses['pending'] }}">{{ $statusLabels[$comment->status] ?? 'Pendiente' }}</span>
                                <span class="rounded-full bg-zinc-100 px-2.5 py-1 text-[11px] font-bold uppercase text-zinc-500 dark:bg-zinc-800 dark:text-zinc-300">{{ $postType }}</span>
                                @if($comment->parent_id)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-sky-100 px-2.5 py-1 text-[11px] font-bold uppercase text-sky-700 dark:bg-sky-950/30 dark:text-sky-300"><i class="fas fa-reply text-[9px]"></i> Nivel {{ $comment->depth + 1 }}</span>
                                @endif
                            </div>

                            @if($comment->parent)
                                <div class="mt-3 rounded-lg border-l-2 border-violet-300 bg-violet-50/60 px-3 py-2 text-xs text-zinc-500 dark:border-violet-700 dark:bg-violet-950/20 dark:text-zinc-400">
                                    <span class="font-semibold text-zinc-600 dark:text-zinc-300">{{ $comment->parent->author_name ?: $comment->parent->user?->name ?: 'Anónimo' }}:</span>
                                    {{ \Illuminate\Support\Str::limit($comment->parent->body, 120) }}
                                </div>
                            @endif

                            <p class="mt-3 whitespace-pre-line text-sm leading-6 text-zinc-800 dark:text-zinc-100">{{ $comment->body }}</p>
                            <div class="mt-4 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-zinc-500 dark:text-zinc-400">
                                <span class="inline-flex items-center gap-1.5"><i class="fas fa-user text-[10px]"></i>{{ $comment->author_name ?: $comment->user?->name ?: 'Anónimo' }}</span>
                                @if($comment->author_email)
                                    <span class="inline-flex items-center gap-1.5"><i class="fas fa-envelope text-[10px]"></i>{{ $comment->author_email }}</span>
                                @endif
                                <span class="inline-flex items-center gap-1.5"><i class="fas fa-calendar text-[10px]"></i>{{ $comment->created_at?->format('d/m/Y H:i') }}</span>
                            </div>
                        </div>
                        <div class="flex min-w-0 flex-col gap-3 lg:w-[360px]">
                            <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-3 dark:border-zinc-800 dark:bg-zinc-950">
                                <p class="truncate text-xs font-semibold uppercase tracking-widest text-zinc-400">Contenido</p>
                                <p class="mt-1 truncate text-sm font-semibold text-zinc-950 dark:text-white">{{ $post?->title ?? 'Contenido eliminado' }}</p>
                                @if($editRoute)
                                    <a href="{{ $editRoute }}" class="mt-2 inline-flex items-center gap-1.5 text-xs font-semibold text-violet-600 hover:text-violet-700 dark:text-violet-300">Abrir editor <i class="fas fa-arrow-up-right-from-square text-[10px]"></i></a>
                                @endif
                            </div>
                            <div class="flex flex-wrap items-center justify-end gap-2">
                                @if($canReply)
                                    <button type="button" wire:click="startReply({{ $comment->id }}, 'content')" class="inline-flex h-9 items-center gap-1.5 rounded-lg border border-violet-200 bg-white px-3 text-xs font-semibold text-violet-600 transition hover:bg-violet-50 dark:border-violet-900/60 dark:bg-zinc-950 dark:text-violet-300"><i class="fas fa-reply text-[10px]"></i>Responder</button>
                                @endif
                                <label class="inline-flex items-center gap-1.5">
                                    <span class="text-[11px] font-semibold uppercase tracking-wide text-zinc-400">Estado</span>
                                    <select wire:change="setStatus({{ $comment->id }}, $event.target.value)" class="h-9 rounded-lg border border-zinc-200 bg-white px-2.5 text-xs font-semibold text-zinc-700 outline-none transition focus:border-violet-300 focus:ring-2 focus:ring-violet-100 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-200">
                                        <option value="approved" @selected($comment->status === 'approved')>Aprobado</option>
                                        <option value="pending" @selected($comment->status === 'pending')>Pendiente</option>
                                        <option value="spam" @selected($comment->status === 'spam')>Spam</option>
                                    </select>
                                </label>
                                <button type="button" @click="window.Starcho?.confirm ? window.Starcho.confirm({ title: 'Eliminar comentario', message: '¿Eliminar este comentario y sus respuestas?', okText: 'Eliminar', cancelText: 'Cancelar', onConfirm: () => $wire.deleteComment({{ $comment->id }}) }) : $wire.deleteComment({{ $comment->id }})" class="inline-flex h-9 items-center gap-1.5 rounded-lg bg-rose-600 px-3 text-xs font-semibold text-white transition hover:bg-rose-700"><i class="fas fa-trash text-[10px]"></i>Eliminar</button>
                            </div>
                        </div>
                    </div>

                    @if($replyTo === $comment->id && $replyScope === 'content')
                        @include('livewire.admin.partials.comment-reply-form')
                    @endif
                </article>
            @empty
                <div class="rounded-2xl border border-dashed border-zinc-300 bg-white p-10 text-center dark:border-zinc-700 dark:bg-zinc-900">
                    <i class="fas fa-comments text-3xl text-zinc-300 dark:text-zinc-600"></i>
                    <p class="mt-3 text-sm font-semibold text-zinc-700 dark:text-zinc-200">No hay comentarios de posts/pages con estos filtros.</p>
                </div>
            @endforelse

            @if($comments)
                {{ $comments->links() }}
            @endif
        </section>
    @endif

    @if($source !== 'content')
        <section class="space-y-3">
            <div class="flex items-center justify-between">
                <h2 class="text-sm font-black uppercase tracking-widest text-zinc-500 dark:text-zinc-400">Multimedia</h2>
                <span class="rounded-full bg-sky-50 px-2.5 py-1 text-xs font-bold text-sky-700 dark:bg-sky-950/30 dark:text-sky-300">{{ $mediaComments?->total() ?? 0 }} comentarios</span>
            </div>

            @forelse($mediaComments ?? [] as $comment)
                @php
                    $target = $comment->commentable;
                    $isAlbum = $target instanceof \App\Models\MediaAlbum;
                    $isMedia = $target instanceof \App\Models\Media;
                    $targetName = $isAlbum ? ($target?->name ?? 'Álbum') : ($target?->display_name ?: $target?->original_name ?? 'Archivo multimedia');
                    $targetUrl = $isAlbum && $target ? route('admin.media.albums.index') : route('admin.media.index');
                    $fileUrl = $isMedia && $target ? $target->public_url : null;
                    $previewUrl = $isMedia && $target ? $target->preview_url : null;
                    $isImage = $isMedia && $target?->isImage();
                    $isVideo = $isMedia && $target?->isVideo();
                    $canReply = ($comment->depth ?? 0) < \App\Models\MediaComment::MAX_DEPTH;
                @endphp

                <article class="group rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm transition hover:border-sky-200 hover:shadow-lg dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-sky-900/60" @if($comment->parent_id) style="margin-left:{{ min($comment->depth ?? 0,2) * 22 }}px" @endif>
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div class="flex min-w-0 flex-1 gap-4">
                            {{-- Preview --}}
                            @if($isImage && $previewUrl)
                                <button type="button" @click='lightbox = { type: "image", src: @js($fileUrl), name: @js($targetName) }' class="relative size-20 shrink-0 overflow-hidden rounded-xl ring-1 ring-zinc-200 dark:ring-zinc-700">
                                    <img src="{{ $previewUrl }}" alt="{{ $targetName }}" class="size-full object-cover">
                                    <span class="absolute inset-0 grid place-items-center bg-black/0 text-white opacity-0 transition group-hover:bg-black/30 group-hover:opacity-100"><i class="fas fa-magnifying-glass-plus text-sm"></i></span>
                                </button>
                            @elseif($isVideo && $fileUrl)
                                <button type="button" @click='lightbox = { type: "video", src: @js($fileUrl), name: @js($targetName) }' class="relative grid size-20 shrink-0 place-items-center overflow-hidden rounded-xl bg-zinc-900 text-white ring-1 ring-zinc-200 dark:ring-zinc-700">
                                    <i class="fas fa-play text-lg"></i>
                                    <span class="absolute bottom-1 right-1 rounded bg-black/60 px-1 text-[9px] font-bold uppercase">Video</span>
                                </button>
                            @elseif($isMedia)
                                <a href="{{ $fileUrl }}" target="_blank" rel="noopener" class="grid size-20 shrink-0 place-items-center rounded-xl bg-zinc-100 text-zinc-400 ring-1 ring-zinc-200 dark:bg-zinc-800 dark:ring-zinc-700"><i class="fas fa-file text-lg"></i></a>
                            @endif

                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="rounded-full px-2.5 py-1 text-[11px] font-bold uppercase ring-1 {{ $statusClasses[$comment->status ?? 'approved'] ?? $statusClasses['pending'] }}">{{ $statusLabels[$comment->status ?? 'approved'] ?? 'Pendiente' }}</span>
                                    <span class="rounded-full bg-sky-100 px-2.5 py-1 text-[11px] font-bold uppercase text-sky-700 dark:bg-sky-950/30 dark:text-sky-300">{{ $isAlbum ? 'Álbum' : 'Archivo' }}</span>
                                    @if($comment->parent_id)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-zinc-100 px-2.5 py-1 text-[11px] font-bold uppercase text-zinc-500 dark:bg-zinc-800 dark:text-zinc-300"><i class="fas fa-reply text-[9px]"></i> Nivel {{ ($comment->depth ?? 0) + 1 }}</span>
                                    @endif
                                </div>

                                @if($comment->parent)
                                    <div class="mt-3 rounded-lg border-l-2 border-sky-300 bg-sky-50/60 px-3 py-2 text-xs text-zinc-500 dark:border-sky-700 dark:bg-sky-950/20 dark:text-zinc-400">
                                        <span class="font-semibold text-zinc-600 dark:text-zinc-300">{{ $comment->parent->user?->name ?: 'Anónimo' }}:</span>
                                        {{ \Illuminate\Support\Str::limit($comment->parent->body, 120) }}
                                    </div>
                                @endif

                                <p class="mt-3 whitespace-pre-line text-sm leading-6 text-zinc-800 dark:text-zinc-100">{{ $comment->body }}</p>
                                <div class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-zinc-500 dark:text-zinc-400">
                                    <span class="inline-flex items-center gap-1.5"><i class="fas fa-user text-[10px]"></i>{{ $comment->user?->name ?: 'Anónimo' }}</span>
                                    <span class="inline-flex items-center gap-1.5"><i class="fas fa-photo-film text-[10px]"></i>{{ \Illuminate\Support\Str::limit($targetName, 40) }}</span>
                                    <span class="inline-flex items-center gap-1.5"><i class="fas fa-calendar text-[10px]"></i>{{ $comment->created_at?->format('d/m/Y H:i') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="flex min-w-0 flex-col gap-3 lg:w-[330px]">
                            <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-3 dark:border-zinc-800 dark:bg-zinc-950">
                                <p class="truncate text-xs font-semibold uppercase tracking-widest text-zinc-400">Multimedia</p>
                                <p class="mt-1 truncate text-sm font-semibold text-zinc-950 dark:text-white">{{ $targetName }}</p>
                                <div class="mt-2 flex items-center gap-3">
                                    @if($fileUrl)
                                        <a href="{{ $fileUrl }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 text-xs font-semibold text-sky-600 hover:text-sky-700 dark:text-sky-300">Abrir archivo <i class="fas fa-arrow-up-right-from-square text-[10px]"></i></a>
                                    @endif
                                    <a href="{{ $targetUrl }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-zinc-500 hover:text-zinc-700 dark:text-zinc-400">Galería <i class="fas fa-arrow-up-right-from-square text-[10px]"></i></a>
                                </div>
                            </div>
                            <div class="flex flex-wrap items-center justify-end gap-2">
                                @if($canReply)
                                    <button type="button" wire:click="startReply({{ $comment->id }}, 'media')" class="inline-flex h-9 items-center gap-1.5 rounded-lg border border-sky-200 bg-white px-3 text-xs font-semibold text-sky-600 transition hover:bg-sky-50 dark:border-sky-900/60 dark:bg-zinc-950 dark:text-sky-300"><i class="fas fa-reply text-[10px]"></i>Responder</button>
                                @endif
                                <label class="inline-flex items-center gap-1.5">
                                    <span class="text-[11px] font-semibold uppercase tracking-wide text-zinc-400">Estado</span>
                                    <select wire:change="setMediaStatus({{ $comment->id }}, $event.target.value)" class="h-9 rounded-lg border border-zinc-200 bg-white px-2.5 text-xs font-semibold text-zinc-700 outline-none transition focus:border-sky-300 focus:ring-2 focus:ring-sky-100 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-200">
                                        <option value="approved" @selected(($comment->status ?? 'approved') === 'approved')>Aprobado</option>
                                        <option value="pending" @selected(($comment->status ?? 'approved') === 'pending')>Pendiente</option>
                                        <option value="spam" @selected(($comment->status ?? 'approved') === 'spam')>Spam</option>
                                    </select>
                                </label>
                                <button type="button" @click="window.Starcho?.confirm ? window.Starcho.confirm({ title: 'Eliminar comentario multimedia', message: '¿Eliminar este comentario y sus respuestas?', okText: 'Eliminar', cancelText: 'Cancelar', onConfirm: () => $wire.deleteMediaComment({{ $comment->id }}) }) : $wire.deleteMediaComment({{ $comment->id }})" class="inline-flex h-9 items-center gap-1.5 rounded-lg bg-rose-600 px-3 text-xs font-semibold text-white transition hover:bg-rose-700"><i class="fas fa-trash text-[10px]"></i>Eliminar</button>
                            </div>
                        </div>
                    </div>

                    @if($replyTo === $comment->id && $replyScope === 'media')
                        @include('livewire.admin.partials.comment-reply-form')
                    @endif
                </article>
            @empty
                <div class="rounded-2xl border border-dashed border-zinc-300 bg-white p-10 text-center dark:border-zinc-700 dark:bg-zinc-900">
                    <i class="fas fa-photo-film text-3xl text-zinc-300 dark:text-zinc-600"></i>
                    <p class="mt-3 text-sm font-semibold text-zinc-700 dark:text-zinc-200">No hay comentarios multimedia con estos filtros.</p>
                </div>
            @endforelse

            @if($mediaComments)
                {{ $mediaComments->links() }}
            @endif
        </section>
    @endif

    {{-- Lightbox --}}
    <div x-cloak x-show="lightbox" @keydown.escape.window="lightbox = null" @click="lightbox = null"
         class="fixed inset-0 z-[60] grid place-items-center bg-black/80 p-4 backdrop-blur-sm"
         x-transition.opacity>
        <div @click.stop class="relative max-h-[90vh] w-full max-w-4xl">
            <button type="button" @click="lightbox = null" class="absolute -top-10 right-0 grid size-9 place-items-center rounded-full bg-white/10 text-white hover:bg-white/20"><i class="fas fa-xmark"></i></button>
            <template x-if="lightbox && lightbox.type === 'image'">
                <img :src="lightbox.src" :alt="lightbox.name" class="mx-auto max-h-[85vh] rounded-xl object-contain">
            </template>
            <template x-if="lightbox && lightbox.type === 'video'">
                <video :src="lightbox.src" controls autoplay class="mx-auto max-h-[85vh] w-full rounded-xl bg-black"></video>
            </template>
            <p x-text="lightbox && lightbox.name" class="mt-3 text-center text-sm font-medium text-white/80"></p>
        </div>
    </div>
</div>
