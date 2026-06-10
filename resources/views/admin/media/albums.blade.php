<x-layouts::admin title="Álbumes Multimedia">

@php
    $fmtBytes = function (int $bytes): string {
        if ($bytes >= 1_073_741_824) return number_format($bytes / 1_073_741_824, 2) . ' GB';
        if ($bytes >= 1_048_576) return number_format($bytes / 1_048_576, 2) . ' MB';
        if ($bytes >= 1024) return number_format($bytes / 1024, 1) . ' KB';
        return $bytes . ' B';
    };

    $typeIcons = ['image' => 'fa-image', 'video' => 'fa-film', 'document' => 'fa-file-lines'];
    $typeBadge = [
        'image' => 'bg-violet-100 text-violet-600 dark:bg-violet-900/30 dark:text-violet-300',
        'video' => 'bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-300',
        'document' => 'bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-300',
    ];

    $albumViewerIds = $selectedAlbum
        ? $selectedAlbum->media->filter(fn ($item) => $item->isImage() || $item->isVideo())->pluck('id')->values()->all()
        : [];
@endphp

<div x-data="mediaAlbumsDashboard()" class="space-y-5">
<section class="rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div class="min-w-0">
            <div class="mb-2 inline-flex items-center gap-2 rounded-full bg-violet-50 px-2.5 py-1 text-xs font-semibold text-violet-700 dark:bg-violet-900/25 dark:text-violet-300">
                <i class="fas fa-folder-tree text-[11px]"></i>
                Biblioteca organizada
            </div>
            <flux:heading size="xl" level="1" class="mb-0.5">Álbumes Multimedia</flux:heading>
            <flux:text class="max-w-3xl text-sm text-zinc-500">
                Administra carpetas, archivos, tags, comentarios, password y calificaciones desde un solo panel.
            </flux:text>
        </div>
        <div class="flex flex-wrap gap-2">
            <flux:button type="button" onclick="Livewire.dispatch('openMediaAlbum', {id: 0})" variant="primary" icon="plus" size="sm">
                Nuevo álbum
            </flux:button>
            <a href="{{ route('admin.media.index') }}"
               class="inline-flex h-9 items-center gap-2 rounded-lg border border-zinc-300 px-4 text-sm font-medium text-zinc-700 transition hover:bg-zinc-50 dark:border-zinc-600 dark:text-zinc-300 dark:hover:bg-zinc-800">
                <i class="fas fa-photo-film text-xs"></i>
                Ver galería
            </a>
        </div>
    </div>
</section>

@include('admin.partials.alerts')

<div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
    @foreach([
        ['label' => 'Álbumes', 'value' => $totals['albums'], 'icon' => 'fa-folder-tree', 'class' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300'],
        ['label' => 'Archivos', 'value' => $totals['files'], 'icon' => 'fa-layer-group', 'class' => 'bg-violet-100 text-violet-700 dark:bg-violet-900/30 dark:text-violet-300'],
        ['label' => 'Sin carpeta', 'value' => $totals['unassigned'], 'icon' => 'fa-inbox', 'class' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300'],
        ['label' => 'Protegidos', 'value' => $totals['protected'], 'icon' => 'fa-lock', 'class' => 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-300'],
    ] as $stat)
        <div class="flex items-center gap-3 rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex size-9 shrink-0 items-center justify-center rounded-lg {{ $stat['class'] }}">
                <i class="fas {{ $stat['icon'] }} text-sm"></i>
            </div>
            <div>
                <div class="text-lg font-bold leading-none text-zinc-800 dark:text-zinc-100">{{ $stat['value'] }}</div>
                <div class="mt-0.5 text-[11px] text-zinc-500">{{ $stat['label'] }}</div>
            </div>
        </div>
    @endforeach
</div>

<div class="grid grid-cols-1 gap-4 xl:grid-cols-[360px_minmax(0,1fr)]">
    <aside class="space-y-4 xl:sticky xl:top-20 xl:self-start">
        <section class="rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="mb-3 flex items-center justify-between gap-3">
                <h2 class="text-sm font-semibold text-zinc-800 dark:text-zinc-100">Álbumes</h2>
                <span class="rounded-full bg-zinc-100 px-2 py-0.5 text-[11px] font-semibold text-zinc-500 dark:bg-zinc-800 dark:text-zinc-300">{{ $albums->count() }}</span>
            </div>
            <div class="max-h-[560px] space-y-2 overflow-y-auto pr-1">
                @forelse($albums as $album)
                    <a href="{{ route('admin.media.albums.index', ['album' => $album->id]) }}"
                       class="block rounded-lg border px-3 py-2.5 transition {{ $selectedAlbum?->id === $album->id ? 'border-violet-400 bg-violet-50 shadow-sm dark:border-violet-600 dark:bg-violet-900/20' : 'border-zinc-200 hover:border-zinc-300 hover:bg-zinc-50 dark:border-zinc-700 dark:hover:border-zinc-600 dark:hover:bg-zinc-800' }}">
                        <div class="flex items-center justify-between gap-3">
                            <div class="min-w-0">
                                <div class="flex min-w-0 items-center gap-2">
                                    <span class="grid size-8 shrink-0 place-items-center rounded-lg {{ $selectedAlbum?->id === $album->id ? 'bg-violet-600 text-white' : 'bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-300' }}">
                                        <i class="fas fa-folder text-xs"></i>
                                    </span>
                                    <span class="truncate text-sm font-semibold text-zinc-800 dark:text-zinc-100">
                                    {{ $album->name }}
                                    </span>
                                    @if($album->password_enabled)
                                        <i class="fas fa-lock text-[10px] text-amber-500"></i>
                                    @endif
                                </div>
                                <div class="mt-1 pl-10 text-[11px] text-zinc-500">
                                    {{ $album->media_count }} archivos
                                    @if($album->ratings_avg_rating)
                                        · {{ number_format($album->ratings_avg_rating, 1) }}/10
                                    @endif
                                </div>
                            </div>
                            <i class="fas fa-chevron-right text-[10px] text-zinc-400"></i>
                        </div>
                    </a>
                @empty
                    <p class="text-sm text-zinc-500">Crea tu primer álbum para empezar.</p>
                @endforelse
            </div>
        </section>
    </aside>

    <main class="space-y-4">
        @if($selectedAlbum)
            <section class="rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <div class="mb-4 flex flex-wrap items-start justify-between gap-4">
                    <div class="min-w-0 flex-1">
                        <div class="mb-2 flex flex-wrap items-center gap-2">
                            <h2 class="truncate text-xl font-bold text-zinc-900 dark:text-zinc-100">{{ $selectedAlbum->name }}</h2>
                            @if($selectedAlbum->password_enabled)
                                <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2 py-0.5 text-[11px] font-semibold text-amber-700 dark:bg-amber-900/25 dark:text-amber-300">
                                    <i class="fas fa-lock text-[10px]"></i>
                                    Protegido
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-[11px] font-semibold text-emerald-700 dark:bg-emerald-900/25 dark:text-emerald-300">
                                    <i class="fas fa-unlock text-[10px]"></i>
                                    Público
                                </span>
                            @endif
                        </div>
                        <p class="max-w-3xl text-sm text-zinc-500">{{ $selectedAlbum->description ?: 'Sin descripción.' }}</p>
                        <div class="mt-2 flex flex-wrap gap-2">
                            @foreach($selectedAlbum->tags as $tag)
                                <span class="rounded bg-zinc-100 px-2 py-1 text-xs text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">#{{ $tag->name }}</span>
                            @endforeach
                        </div>
                        <div class="mt-3 flex flex-wrap gap-2 text-xs">
                            <span class="rounded-lg bg-zinc-100 px-2.5 py-1 font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">{{ $selectedAlbum->media->count() }} archivos</span>
                            <span class="rounded-lg bg-zinc-100 px-2.5 py-1 font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">{{ $selectedAlbum->comments->count() }} comentarios</span>
                            <span class="rounded-lg bg-zinc-100 px-2.5 py-1 font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">{{ $selectedAlbum->average_rating ? $selectedAlbum->average_rating . '/10' : 'Sin calificar' }}</span>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('media.albums.show', ['album' => $selectedAlbum->slug]) }}" target="_blank"
                           class="inline-flex h-8 items-center gap-2 rounded-lg border border-zinc-300 px-3 text-xs font-medium text-zinc-700 hover:bg-zinc-50 dark:border-zinc-600 dark:text-zinc-300 dark:hover:bg-zinc-800">
                            <i class="fas fa-arrow-up-right-from-square text-[10px]"></i>
                            Público
                        </a>
                        <button type="button"
                                onclick="Livewire.dispatch('openMediaAlbum', {id: {{ $selectedAlbum->id }}})"
                                class="inline-flex h-8 items-center gap-2 rounded-lg border border-zinc-300 px-3 text-xs font-medium text-zinc-700 hover:bg-zinc-50 dark:border-zinc-600 dark:text-zinc-300 dark:hover:bg-zinc-800">
                            <i class="fas fa-pen text-[10px]"></i>
                            Editar
                        </button>
                        <form method="POST" action="{{ route('admin.media.albums.destroy', $selectedAlbum) }}" data-confirm-title="Eliminar álbum" data-confirm-message="¿Eliminar este álbum? Los archivos no se eliminan.">
                            @csrf @method('DELETE')
                            <button class="inline-flex h-8 items-center gap-2 rounded-lg border border-rose-300 px-3 text-xs font-medium text-rose-600 hover:bg-rose-50 dark:border-rose-700 dark:hover:bg-rose-900/20">
                                <i class="fas fa-trash text-[10px]"></i>
                                Eliminar álbum
                            </button>
                        </form>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-3 lg:grid-cols-3">
                    <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-3 dark:border-zinc-700 dark:bg-zinc-800/40">
                        <h3 class="mb-2 text-xs font-semibold uppercase tracking-wide text-zinc-400">Subir archivos</h3>
                        <form method="POST" action="{{ route('admin.media.albums.upload', $selectedAlbum) }}" enctype="multipart/form-data" class="space-y-2">
                            @csrf
                            <input type="file" name="files[]" multiple required
                                   class="block w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-800">
                            <button class="inline-flex h-9 items-center gap-2 rounded-lg bg-violet-600 px-4 text-sm font-semibold text-white hover:bg-violet-700">
                                <i class="fas fa-upload text-xs"></i>
                                Subir al álbum
                            </button>
                        </form>
                    </div>

                    <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-3 dark:border-zinc-700 dark:bg-zinc-800/40">
                        <h3 class="mb-2 text-xs font-semibold uppercase tracking-wide text-zinc-400">Agregar existentes</h3>
                        <form method="POST" action="{{ route('admin.media.albums.attach', $selectedAlbum) }}" class="space-y-2">
                            @csrf
                            <select name="media_ids[]" multiple required class="min-h-24 w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-800">
                                @foreach($availableMedia as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }} @if($item->albums->isNotEmpty())({{ $item->albums->pluck('name')->implode(', ') }})@endif</option>
                                @endforeach
                            </select>
                            <button class="inline-flex h-9 items-center gap-2 rounded-lg border border-zinc-300 px-4 text-sm font-medium text-zinc-700 hover:bg-zinc-50 dark:border-zinc-600 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                <i class="fas fa-plus text-xs"></i>
                                Agregar existentes
                            </button>
                        </form>
                    </div>

                    <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-3 dark:border-zinc-700 dark:bg-zinc-800/40">
                        <h3 class="mb-2 text-xs font-semibold uppercase tracking-wide text-zinc-400">Feedback del álbum</h3>
                        <form method="POST" action="{{ route('admin.media.albums.rating.store', ['type' => 'album', 'id' => $selectedAlbum->id]) }}" class="flex items-center gap-2">
                            @csrf
                            <input type="number" name="rating" min="1" max="10" value="{{ optional($selectedAlbum->ratings->firstWhere('user_id', auth()->id()))->rating }}"
                                   class="h-9 w-20 rounded-lg border border-zinc-200 bg-white px-3 text-sm dark:border-zinc-700 dark:bg-zinc-800">
                            <button class="h-9 rounded-lg border border-zinc-300 px-4 text-sm text-zinc-700 dark:border-zinc-600 dark:text-zinc-300">Calificar</button>
                        </form>

                        <form method="POST" action="{{ route('admin.media.albums.comments.store', ['type' => 'album', 'id' => $selectedAlbum->id]) }}" class="flex gap-2">
                            @csrf
                            <input name="body" placeholder="Comentar álbum" class="h-9 min-w-0 flex-1 rounded-lg border border-zinc-200 bg-white px-3 text-sm dark:border-zinc-700 dark:bg-zinc-800">
                            <button class="h-9 rounded-lg border border-zinc-300 px-4 text-sm text-zinc-700 dark:border-zinc-600 dark:text-zinc-300">Enviar</button>
                        </form>
                    </div>
                </div>
            </section>

            <section class="rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h3 class="text-sm font-semibold text-zinc-800 dark:text-zinc-100">Archivos en este álbum</h3>
                        <span class="text-xs text-zinc-500">{{ $selectedAlbum->media->count() }} archivos</span>
                    </div>
                    <div class="flex items-center gap-2 rounded-lg border border-zinc-200 p-1 dark:border-zinc-700">
                        <button type="button" @click="viewMode = 'grid'" :class="viewMode === 'grid' ? 'bg-zinc-900 text-white dark:bg-white dark:text-zinc-900' : 'text-zinc-500'" class="flex size-8 items-center justify-center rounded-md transition" title="Grilla">
                            <i class="fas fa-grip text-xs"></i>
                        </button>
                        <button type="button" @click="viewMode = 'list'" :class="viewMode === 'list' ? 'bg-zinc-900 text-white dark:bg-white dark:text-zinc-900' : 'text-zinc-500'" class="flex size-8 items-center justify-center rounded-md transition" title="Lista">
                            <i class="fas fa-list text-xs"></i>
                        </button>
                    </div>
                </div>

                <div x-show="selected.length > 0" x-cloak class="mb-4 rounded-lg border border-violet-200 bg-violet-50 p-3 dark:border-violet-700/50 dark:bg-violet-900/10">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <span class="text-sm font-semibold text-violet-700 dark:text-violet-300" x-text="selected.length + ' seleccionado(s)'"></span>
                        <div class="flex flex-wrap gap-2">
                            <form method="POST" action="{{ route('admin.media.albums.files.bulk-detach', $selectedAlbum) }}" x-ref="bulkDetachForm">
                                @csrf
                                <template x-for="id in selected" :key="'detach-' + id">
                                    <input type="hidden" name="media_ids[]" :value="id">
                                </template>
                                <button type="button" @click="confirmSubmit($refs.bulkDetachForm, 'Quitar del álbum', '¿Quitar los archivos seleccionados de este álbum?')" class="h-9 rounded-lg border border-zinc-300 px-4 text-sm font-medium text-zinc-700 hover:bg-zinc-50 dark:border-zinc-600 dark:text-zinc-300 dark:hover:bg-zinc-800">Quitar del álbum</button>
                            </form>

                            <form method="POST" action="{{ route('admin.media.albums.files.bulk-destroy') }}" x-ref="bulkDestroyForm">
                                @csrf
                                <template x-for="id in selected" :key="'destroy-' + id">
                                    <input type="hidden" name="media_ids[]" :value="id">
                                </template>
                                <button type="button" @click="confirmSubmit($refs.bulkDestroyForm, 'Eliminar selección', '¿Eliminar en cascada los archivos seleccionados del storage? Esta acción no se puede deshacer.')" class="h-9 rounded-lg bg-rose-600 px-4 text-sm font-semibold text-white hover:bg-rose-700">Eliminar en cascada</button>
                            </form>
                        </div>
                    </div>
                </div>

                @if($selectedAlbum->media->isEmpty())
                    <div class="py-16 text-center text-zinc-400">
                        <i class="fas fa-folder-open mb-3 block text-4xl"></i>
                        <p class="text-sm">Este álbum todavía no tiene archivos.</p>
                    </div>
                @else
                    <div x-show="viewMode === 'grid'" class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4 2xl:grid-cols-5">
                        @foreach($selectedAlbum->media as $item)
                            @php
                                $ftype = $item->fileType();
                                $icon = $typeIcons[$ftype] ?? 'fa-file';
                                $badge = $typeBadge[$ftype] ?? 'bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300';
                                $itemTags = $item->tags->pluck('name')->implode(', ');
                                $moveAlbums = $albums->where('id', '!=', $selectedAlbum->id);
                            @endphp
                            <article class="group relative overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm transition duration-300 hover:z-20 hover:-translate-y-1 hover:border-zinc-300 hover:shadow-2xl dark:border-zinc-700 dark:bg-zinc-900 dark:hover:border-zinc-500">
                                <div class="relative w-full overflow-hidden bg-zinc-950" style="aspect-ratio: 1 / 1;">
                                    <label
                                        class="absolute left-2 top-2 z-20 grid size-8 cursor-pointer place-items-center rounded-full bg-black/45 text-white shadow ring-1 ring-white/20 backdrop-blur transition"
                                        :class="selected.includes({{ $item->id }}) ? 'opacity-100' : 'opacity-0 group-hover:opacity-100'"
                                        @click.stop
                                        title="Seleccionar"
                                    >
                                        <input type="checkbox" class="peer sr-only" value="{{ $item->id }}" @change="toggle({{ $item->id }}, $event.target.checked)" :checked="selected.includes({{ $item->id }})">
                                        <span class="grid size-6 place-items-center rounded-full border border-white/70 bg-black/30 text-transparent transition peer-checked:border-[#1db954] peer-checked:bg-[#1db954] peer-checked:text-black">
                                            <flux:icon.check class="size-4 stroke-[2.4]" />
                                        </span>
                                    </label>

                                    @if($item->isImage() || $item->isVideo())
                                        <button type="button" onclick="Livewire.dispatch('openAdminMediaViewer', {id: {{ $item->id }}, ids: @js($albumViewerIds)})" class="block h-full w-full overflow-hidden">
                                    @else
                                        <a href="{{ $item->public_url }}" target="_blank" rel="noopener" class="block h-full w-full overflow-hidden">
                                    @endif
                                        @if($item->isImage())
                                            <img src="{{ $item->preview_url }}" alt="{{ $item->alt ?? $item->name }}" class="block h-full w-full object-cover transition-transform duration-300 group-hover:scale-105" style="width: 100%; height: 100%; object-fit: cover;" loading="lazy">
                                        @elseif($item->isVideo())
                                            <video src="{{ $item->public_url }}" class="block h-full w-full object-cover" style="width: 100%; height: 100%; object-fit: cover;" preload="metadata"></video>
                                        @else
                                            <div class="flex h-full w-full items-center justify-center {{ $badge }}">
                                                <i class="fas {{ $icon }} text-3xl"></i>
                                            </div>
                                        @endif
                                    @if($item->isImage() || $item->isVideo())
                                        </button>
                                    @else
                                        </a>
                                    @endif

                                    <span class="absolute right-2 top-2 z-10 inline-flex items-center gap-1 rounded-full bg-black/60 px-1.5 py-0.5 text-[9px] font-semibold uppercase text-white shadow-sm backdrop-blur">
                                        <i class="fas {{ $icon }} text-[8px]"></i>{{ $ftype }}
                                    </span>

                                    <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/20 to-transparent opacity-70 transition-opacity duration-300 group-hover:opacity-100"></div>
                                    <div class="absolute bottom-3 left-2 right-2 z-10 transition-all duration-300 group-hover:bottom-4" @click.stop>
                                        <p class="mb-2 truncate text-sm font-bold text-white drop-shadow" title="{{ $item->name }}">{{ $item->name }}</p>
                                        <div class="flex flex-wrap items-center justify-center gap-1 rounded-2xl bg-black/45 p-1 shadow-xl ring-1 ring-white/10 backdrop-blur">
                                            @if($item->isImage() || $item->isVideo())
                                                <button type="button" onclick="Livewire.dispatch('openAdminMediaViewer', {id: {{ $item->id }}, ids: @js($albumViewerIds)})" class="grid size-8 place-items-center rounded-full bg-white text-zinc-950 shadow-lg transition hover:scale-110" title="Ver">
                                                    <i class="fas {{ $item->isVideo() ? 'fa-play' : 'fa-eye' }} text-xs leading-none"></i>
                                                </button>
                                            @else
                                                <a href="{{ $item->public_url }}" target="_blank" rel="noopener" class="grid size-8 place-items-center rounded-full bg-white text-zinc-950 shadow-lg transition hover:scale-110" title="Abrir">
                                                    <i class="fas fa-arrow-up-right-from-square text-xs leading-none"></i>
                                                </a>
                                            @endif
                                            <a href="{{ route('admin.media.download', $item) }}" class="grid size-8 place-items-center rounded-full bg-zinc-900/75 text-white ring-1 ring-white/25 backdrop-blur transition hover:bg-white hover:text-zinc-950" title="Descargar">
                                                <i class="fas fa-download text-xs leading-none"></i>
                                            </a>
                                            <button type="button" data-copy-url="{{ e($item->public_url) }}" onclick="copyAlbumUrl(this.dataset.copyUrl)" class="grid size-8 place-items-center rounded-full bg-zinc-900/75 text-white ring-1 ring-white/25 backdrop-blur transition hover:bg-white hover:text-zinc-950" title="Copiar URL">
                                                <i class="fas fa-copy text-xs leading-none"></i>
                                            </button>
                                            <button type="button" onclick="Livewire.dispatch('openMediaTags', {id: {{ $item->id }}})" class="grid size-8 place-items-center rounded-full bg-zinc-900/75 text-white ring-1 ring-white/25 backdrop-blur transition hover:bg-white hover:text-zinc-950" title="Tags">
                                                <i class="fas fa-tags text-xs leading-none"></i>
                                            </button>
                                            <form method="POST" action="{{ route('admin.media.albums.files.detach', [$selectedAlbum, $item]) }}" data-confirm-title="Quitar del álbum" data-confirm-message="¿Quitar este archivo de este álbum?">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="grid size-8 place-items-center rounded-full bg-zinc-900/75 text-white ring-1 ring-white/25 backdrop-blur transition hover:bg-white hover:text-zinc-950" title="Quitar">
                                                    <i class="fas fa-folder-minus text-xs leading-none"></i>
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.media.albums.files.destroy', $item) }}" data-confirm-title="Eliminar archivo" data-confirm-message="¿Eliminar este archivo del storage? Esta acción no se puede deshacer.">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="grid size-8 place-items-center rounded-full bg-rose-600/90 text-white ring-1 ring-white/20 backdrop-blur transition hover:bg-rose-500" title="Eliminar">
                                                    <i class="fas fa-trash text-xs leading-none"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <div class="space-y-2 border-t border-zinc-100 px-2.5 py-2 dark:border-zinc-800">
                                    <p class="truncate text-xs font-medium text-zinc-700 dark:text-zinc-300" title="{{ $item->name }}">{{ $item->name }}</p>
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="text-[10px] text-zinc-400">{{ $item->sizeLabel() }}</span>
                                        @if($item->width && $item->height)
                                            <span class="text-[10px] text-zinc-400">{{ $item->width }}x{{ $item->height }}</span>
                                        @endif
                                    </div>
                                    @if($item->tags->isNotEmpty())
                                        <p class="truncate text-[10px] text-violet-600 dark:text-violet-400">#{{ $item->tags->pluck('name')->implode(' #') }}</p>
                                    @else
                                        <button type="button" onclick="Livewire.dispatch('openMediaTags', {id: {{ $item->id }}})" class="text-[10px] font-medium text-zinc-400 hover:text-violet-500">Agregar tags</button>
                                    @endif
                                </div>

                                <details class="border-t border-zinc-100 px-2.5 py-2 dark:border-zinc-800">
                                    <summary class="cursor-pointer rounded-lg px-2 py-1 text-xs font-semibold text-violet-600 transition hover:bg-violet-50 dark:text-violet-400 dark:hover:bg-violet-900/20">Editar datos</summary>
                                    <div class="mt-3 space-y-3 rounded-lg bg-zinc-50 p-2 dark:bg-zinc-800/50">
                                        <form method="POST" action="{{ route('admin.media.albums.files.update', $item) }}" class="space-y-2">
                                            @csrf @method('PUT')
                                            <input name="display_name" value="{{ $item->display_name }}" placeholder="Nombre de referencia" class="h-8 w-full rounded-lg border border-zinc-200 bg-white px-2 text-xs dark:border-zinc-700 dark:bg-zinc-800">
                                            <input name="alt" value="{{ $item->alt }}" placeholder="Alt" class="h-8 w-full rounded-lg border border-zinc-200 bg-white px-2 text-xs dark:border-zinc-700 dark:bg-zinc-800">
                                            <textarea name="caption" rows="2" placeholder="Caption" class="w-full rounded-lg border border-zinc-200 bg-white px-2 py-1 text-xs dark:border-zinc-700 dark:bg-zinc-800">{{ $item->caption }}</textarea>
                                            <input name="tags" value="{{ $itemTags }}" placeholder="tags separados por coma" class="h-8 w-full rounded-lg border border-zinc-200 bg-white px-2 text-xs dark:border-zinc-700 dark:bg-zinc-800">
                                            <button class="h-8 rounded-lg bg-zinc-900 px-3 text-xs font-semibold text-white dark:bg-white dark:text-zinc-900">Guardar archivo</button>
                                        </form>

                                        @if($moveAlbums->isNotEmpty())
                                            <form method="POST" action="{{ route('admin.media.albums.files.move', [$selectedAlbum, $item]) }}" class="flex gap-2">
                                                @csrf @method('PATCH')
                                                <select name="target_album_id" class="h-8 min-w-0 flex-1 rounded-lg border border-zinc-200 bg-white px-2 text-xs dark:border-zinc-700 dark:bg-zinc-800">
                                                    @foreach($moveAlbums as $album)
                                                        <option value="{{ $album->id }}">{{ $album->name }}</option>
                                                    @endforeach
                                                </select>
                                                <button class="h-8 rounded-lg border border-zinc-300 px-3 text-xs text-zinc-700 dark:border-zinc-600 dark:text-zinc-300">Mover</button>
                                            </form>
                                        @endif

                                        <form method="POST" action="{{ route('admin.media.albums.rating.store', ['type' => 'media', 'id' => $item->id]) }}" class="flex items-center gap-2">
                                            @csrf
                                            <input type="number" name="rating" min="1" max="10" value="{{ optional($item->ratings->firstWhere('user_id', auth()->id()))->rating }}" class="h-8 w-16 rounded-lg border border-zinc-200 bg-white px-2 text-xs dark:border-zinc-700 dark:bg-zinc-800">
                                            <button class="h-8 rounded-lg border border-zinc-300 px-3 text-xs text-zinc-700 dark:border-zinc-600 dark:text-zinc-300">Calificar</button>
                                        </form>

                                        <form method="POST" action="{{ route('admin.media.albums.comments.store', ['type' => 'media', 'id' => $item->id]) }}" class="flex gap-2">
                                            @csrf
                                            <input name="body" placeholder="Comentar archivo" class="h-8 min-w-0 flex-1 rounded-lg border border-zinc-200 bg-white px-2 text-xs dark:border-zinc-700 dark:bg-zinc-800">
                                            <button class="h-8 rounded-lg border border-zinc-300 px-3 text-xs text-zinc-700 dark:border-zinc-600 dark:text-zinc-300">Enviar</button>
                                        </form>

                                        @foreach($item->comments->take(2) as $comment)
                                            <div class="border-l-2 border-zinc-200 pl-2 text-[11px] text-zinc-500 dark:border-zinc-700">{{ $comment->body }}</div>
                                        @endforeach
                                    </div>
                                </details>
                            </article>
                        @endforeach
                    </div>

                    <div x-show="viewMode === 'list'" x-cloak class="overflow-x-auto rounded-lg border border-zinc-200 dark:border-zinc-700">
                        <table class="min-w-full divide-y divide-zinc-200 text-sm dark:divide-zinc-800">
                            <thead class="bg-zinc-50 text-xs uppercase tracking-wide text-zinc-400 dark:bg-zinc-900">
                                <tr>
                                    <th class="w-12 px-4 py-3"></th>
                                    <th class="px-4 py-3 text-left">Archivo</th>
                                    <th class="px-4 py-3 text-left">Tags</th>
                                    <th class="px-4 py-3 text-left">Tipo</th>
                                    <th class="px-4 py-3 text-left">Tamaño</th>
                                    <th class="px-4 py-3 text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-100 bg-white dark:divide-zinc-800 dark:bg-zinc-900">
                                @foreach($selectedAlbum->media as $item)
                                    @php
                                        $ftype = $item->fileType();
                                        $icon = $typeIcons[$ftype] ?? 'fa-file';
                                    @endphp
                                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/70">
                                        <td class="px-4 py-3">
                                            <label class="grid size-8 cursor-pointer place-items-center rounded-full bg-zinc-50 dark:bg-zinc-800" title="Seleccionar">
                                                <input type="checkbox" class="peer sr-only" value="{{ $item->id }}" @change="toggle({{ $item->id }}, $event.target.checked)" :checked="selected.includes({{ $item->id }})">
                                                <span class="grid size-6 place-items-center rounded-full border border-zinc-300 bg-white text-transparent transition peer-checked:border-[#1db954] peer-checked:bg-[#1db954] peer-checked:text-black dark:border-zinc-600 dark:bg-zinc-900">
                                                    <flux:icon.check class="size-4 stroke-[2.4]" />
                                                </span>
                                            </label>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center gap-3">
                                                @if($item->isImage() || $item->isVideo())
                                                    <button type="button" onclick="Livewire.dispatch('openAdminMediaViewer', {id: {{ $item->id }}, ids: @js($albumViewerIds)})" class="size-12 shrink-0 overflow-hidden rounded-lg bg-zinc-100 dark:bg-zinc-800" style="width: 3rem; height: 3rem;">
                                                @else
                                                    <a href="{{ $item->public_url }}" target="_blank" rel="noopener" class="block size-12 shrink-0 overflow-hidden rounded-lg bg-zinc-100 dark:bg-zinc-800" style="width: 3rem; height: 3rem;">
                                                @endif
                                                    @if($item->isImage())
                                                        <img src="{{ $item->preview_url }}" alt="{{ $item->alt ?? $item->name }}" class="block h-full w-full object-cover" style="width: 100%; height: 100%; object-fit: cover;">
                                                    @elseif($item->isVideo())
                                                        <video src="{{ $item->public_url }}" class="block h-full w-full object-cover" style="width: 100%; height: 100%; object-fit: cover;" preload="metadata"></video>
                                                    @else
                                                        <span class="flex h-full w-full items-center justify-center text-zinc-400"><i class="fas {{ $icon }}"></i></span>
                                                    @endif
                                                @if($item->isImage() || $item->isVideo())
                                                    </button>
                                                @else
                                                    </a>
                                                @endif
                                                <div class="min-w-0">
                                                    <p class="truncate font-medium text-zinc-800 dark:text-zinc-100">{{ $item->name }}</p>
                                                    <p class="truncate text-xs text-zinc-500">{{ $item->original_name }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="max-w-xs px-4 py-3">
                                            <div class="flex flex-wrap gap-1">
                                                @forelse($item->tags as $tag)
                                                    <span class="rounded bg-violet-50 px-1.5 py-0.5 text-[11px] text-violet-600 dark:bg-violet-900/30 dark:text-violet-300">#{{ $tag->name }}</span>
                                                @empty
                                                    <span class="text-xs text-zinc-400">Sin tags</span>
                                                @endforelse
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-zinc-500">{{ $ftype }}</td>
                                        <td class="px-4 py-3 text-zinc-500">{{ $item->sizeLabel() }}</td>
                                        <td class="px-4 py-3">
                                            <div class="flex justify-end gap-1">
                                                @if($item->isImage() || $item->isVideo())
                                                    <button type="button" onclick="Livewire.dispatch('openAdminMediaViewer', {id: {{ $item->id }}, ids: @js($albumViewerIds)})" class="flex size-8 items-center justify-center rounded-lg border border-zinc-200 text-zinc-600 hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-300 dark:hover:bg-zinc-800" title="Ver">
                                                        <i class="fas fa-eye text-xs"></i>
                                                    </button>
                                                @else
                                                    <a href="{{ $item->public_url }}" target="_blank" rel="noopener" class="flex size-8 items-center justify-center rounded-lg border border-zinc-200 text-zinc-600 hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-300 dark:hover:bg-zinc-800" title="Abrir">
                                                        <i class="fas fa-arrow-up-right-from-square text-xs"></i>
                                                    </a>
                                                @endif
                                                <a href="{{ route('admin.media.download', $item) }}" class="flex size-8 items-center justify-center rounded-lg border border-zinc-200 text-zinc-600 hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-300 dark:hover:bg-zinc-800" title="Descargar"><i class="fas fa-download text-xs"></i></a>
                                                <button type="button" data-copy-url="{{ e($item->public_url) }}" onclick="copyAlbumUrl(this.dataset.copyUrl)" class="flex size-8 items-center justify-center rounded-lg border border-zinc-200 text-zinc-600 hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-300 dark:hover:bg-zinc-800" title="Copiar URL"><i class="fas fa-copy text-xs"></i></button>
                                                <button type="button" onclick="Livewire.dispatch('openMediaTags', {id: {{ $item->id }}})" class="flex size-8 items-center justify-center rounded-lg border border-violet-200 text-violet-600 hover:bg-violet-50 dark:border-violet-700 dark:text-violet-300 dark:hover:bg-violet-900/20" title="Tags">
                                                    <i class="fas fa-tags text-xs"></i>
                                                </button>
                                                <form method="POST" action="{{ route('admin.media.albums.files.detach', [$selectedAlbum, $item]) }}" data-confirm-title="Quitar del álbum" data-confirm-message="¿Quitar este archivo de este álbum?">
                                                    @csrf @method('DELETE')
                                                    <button class="flex size-8 items-center justify-center rounded-lg border border-zinc-200 text-zinc-600 hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-300 dark:hover:bg-zinc-800" title="Quitar"><i class="fas fa-folder-minus text-xs"></i></button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>
        @else
            <section class="rounded-xl border border-zinc-200 bg-white p-12 text-center shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <i class="fas fa-folder-plus mb-4 block text-5xl text-zinc-300"></i>
                <h2 class="text-lg font-semibold text-zinc-800 dark:text-zinc-100">Crea un álbum para organizar tu multimedia</h2>
                <p class="mt-1 text-sm text-zinc-500">Cuando tengas carpetas, aquí podrás subir, mover y administrar archivos.</p>
            </section>
        @endif

        <section class="rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                <h3 class="text-sm font-semibold text-zinc-800 dark:text-zinc-100">Biblioteca completa</h3>
                <form method="GET" action="{{ route('admin.media.albums.index') }}" class="flex flex-wrap gap-2">
                    @if($selectedAlbum)
                        <input type="hidden" name="album" value="{{ $selectedAlbum->id }}">
                    @endif
                    <input name="q" value="{{ request('q') }}" placeholder="Buscar archivos" class="h-8 w-48 rounded-lg border border-zinc-200 bg-white px-2 text-xs dark:border-zinc-700 dark:bg-zinc-800">
                    <select name="type" class="h-8 rounded-lg border border-zinc-200 bg-white px-2 text-xs dark:border-zinc-700 dark:bg-zinc-800">
                        <option value="">Tipo</option>
                        <option value="image" @selected(request('type') === 'image')>Imágenes</option>
                        <option value="video" @selected(request('type') === 'video')>Videos</option>
                        <option value="application" @selected(request('type') === 'application')>Documentos</option>
                    </select>
                    <button class="h-8 rounded-lg border border-zinc-300 px-3 text-xs text-zinc-700 dark:border-zinc-600 dark:text-zinc-300">Filtrar</button>
                </form>
            </div>

            <div class="grid grid-cols-1 gap-2 md:grid-cols-2 xl:grid-cols-3">
                @foreach($media as $item)
                    <div class="flex items-center justify-between gap-3 rounded-lg border border-zinc-200 bg-white p-2 transition hover:border-zinc-300 hover:shadow-sm dark:border-zinc-700 dark:bg-zinc-900 dark:hover:border-zinc-600">
                        <div class="flex min-w-0 items-center gap-3">
                            <div class="size-11 shrink-0 overflow-hidden rounded-lg bg-zinc-100 dark:bg-zinc-800" style="width: 2.75rem; height: 2.75rem;">
                                @if($item->isImage())
                                    <img src="{{ $item->preview_url }}" alt="{{ $item->alt ?? $item->name }}" class="block h-full w-full object-cover" style="width: 100%; height: 100%; object-fit: cover;">
                                @elseif($item->isVideo())
                                    <video src="{{ $item->public_url }}" class="block h-full w-full object-cover" style="width: 100%; height: 100%; object-fit: cover;" preload="metadata"></video>
                                @else
                                    <span class="flex h-full w-full items-center justify-center text-zinc-400"><i class="fas fa-file-lines"></i></span>
                                @endif
                            </div>
                            <div class="min-w-0">
                            <p class="truncate text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ $item->name }}</p>
                            <p class="truncate text-[11px] text-zinc-500">{{ $item->albums->isNotEmpty() ? $item->albums->pluck('name')->implode(', ') : 'Sin carpeta' }}</p>
                            </div>
                        </div>
                        <div class="flex shrink-0 gap-1">
                            <button type="button" data-copy-url="{{ e($item->public_url) }}" onclick="copyAlbumUrl(this.dataset.copyUrl)" class="grid size-8 place-items-center rounded-lg border border-zinc-200 text-zinc-500 transition hover:bg-zinc-50 hover:text-zinc-800 dark:border-zinc-700 dark:hover:bg-zinc-800" title="Copiar URL">
                                <i class="fas fa-copy text-xs"></i>
                            </button>
                            @if($selectedAlbum && ! $item->albums->contains($selectedAlbum->id))
                                <form method="POST" action="{{ route('admin.media.albums.attach', $selectedAlbum) }}">
                                    @csrf
                                    <input type="hidden" name="media_ids[]" value="{{ $item->id }}">
                                    <button class="grid size-8 place-items-center rounded-lg border border-violet-200 text-xs text-violet-600 hover:bg-violet-50 dark:border-violet-700 dark:text-violet-300 dark:hover:bg-violet-900/20" title="Agregar al álbum">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            @if($media->hasPages())
                <div class="mt-4">{{ $media->links() }}</div>
            @endif
        </section>
    </main>
</div>

<livewire:admin.media-album-modal />
<livewire:admin.media-tags-modal />
<livewire:admin.media-viewer />

<div id="album-copy-toast" class="fixed bottom-6 right-6 z-50 hidden items-center gap-2 rounded-xl bg-zinc-900 px-4 py-2.5 text-sm text-white shadow-lg">
    <i class="fas fa-check text-xs text-emerald-400"></i>
    URL copiada al portapapeles
</div>

</div>

<script>
function mediaAlbumsDashboard() {
    return {
        viewMode: localStorage.getItem('adminMediaAlbumsViewMode') || 'grid',
        selected: [],
        toggle(id, checked) {
            id = Number(id);
            this.selected = checked
                ? [...new Set([...this.selected, id])]
                : this.selected.filter(item => item !== id);
        },
        confirmSubmit(form, title, message) {
            window.Starcho.confirm({
                title,
                message,
                okText: 'Sí, continuar',
                cancelText: 'Cancelar',
                onConfirm: () => form.submit(),
            });
        },
        init() {
            this.$watch('viewMode', value => localStorage.setItem('adminMediaAlbumsViewMode', value));
        },
    };
}

async function copyAlbumUrl(url) {
    const text = decodeAlbumCopyHtml(String(url || ''));

    try {
        if (navigator.clipboard && window.isSecureContext) {
            await navigator.clipboard.writeText(text);
        } else {
            fallbackAlbumCopyText(text);
        }

        const toast = document.getElementById('album-copy-toast');
        toast.classList.remove('hidden');
        toast.classList.add('flex');
        setTimeout(() => {
            toast.classList.add('hidden');
            toast.classList.remove('flex');
        }, 2200);
    } catch (error) {
        window.Starcho?.notify?.('error', 'No se pudo copiar la URL.');
    }
}

function decodeAlbumCopyHtml(value) {
    const textarea = document.createElement('textarea');
    textarea.innerHTML = value;

    return textarea.value;
}

function fallbackAlbumCopyText(value) {
    const textarea = document.createElement('textarea');
    textarea.value = value;
    textarea.setAttribute('readonly', 'readonly');
    textarea.style.position = 'fixed';
    textarea.style.opacity = '0';
    textarea.style.pointerEvents = 'none';
    document.body.appendChild(textarea);
    textarea.select();
    textarea.setSelectionRange(0, textarea.value.length);

    const copied = document.execCommand('copy');
    textarea.remove();

    if (!copied) {
        throw new Error('copy_failed');
    }
}

document.addEventListener('submit', event => {
    const form = event.target;
    if (!form.matches('[data-confirm-message]')) return;

    event.preventDefault();
    window.Starcho.confirm({
        title: form.dataset.confirmTitle || 'Confirmar acción',
        message: form.dataset.confirmMessage || '¿Continuar?',
        okText: 'Sí, continuar',
        cancelText: 'Cancelar',
        onConfirm: () => form.submit(),
    });
});
</script>

</x-layouts::admin>
