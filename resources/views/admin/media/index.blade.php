<x-layouts::admin title="Galería Multimedia">

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

    $viewerIds = $media->getCollection()
        ->filter(fn ($item) => $item->isImage() || $item->isVideo())
        ->values()
        ->pluck('id')
        ->all();
@endphp

<div
    x-data="mediaLibrary()"
    class="space-y-5"
>
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <flux:heading size="xl" level="1" class="mb-0.5">Galería Multimedia</flux:heading>
            <flux:text class="text-sm text-zinc-500">
                Driver: <span class="font-semibold text-zinc-700 dark:text-zinc-300">{{ ucfirst($storageSetting->default_driver) }}</span>
                @if($storageSetting->isLocal())
                    · URL: <code class="rounded bg-zinc-100 px-1.5 py-0.5 font-mono text-xs dark:bg-zinc-800">{{ $storageSetting->localBaseUrl() }}/storage/</code>
                @endif
            </flux:text>
        </div>

        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.media.albums.index') }}"
               class="inline-flex h-9 items-center gap-2 rounded-lg border border-zinc-300 px-4 text-sm font-medium text-zinc-700 transition hover:bg-zinc-50 dark:border-zinc-600 dark:text-zinc-300 dark:hover:bg-zinc-800">
                <i class="fas fa-folder-tree text-xs"></i>
                Gestionar álbumes
            </a>
            <label for="bulk-upload"
                   class="inline-flex h-9 cursor-pointer items-center gap-2 rounded-lg bg-violet-600 px-4 text-sm font-medium text-white shadow-sm shadow-violet-500/25 transition hover:bg-violet-700">
                <i class="fas fa-upload text-xs"></i>
                Subir archivos
            </label>
            <input type="file" id="bulk-upload" class="hidden" multiple
                   accept="image/*,video/*,application/pdf,.doc,.docx,.xls,.xlsx,.zip"
                   onchange="bulkUpload(this.files)">
        </div>
    </div>

    @include('admin.partials.alerts')

    <div id="upload-area" class="hidden rounded-xl border border-violet-200 bg-violet-50 p-4 dark:border-violet-700/50 dark:bg-violet-900/10">
        <div class="mb-2 flex items-center gap-3">
            <div class="size-4 animate-spin rounded-full border-2 border-violet-600 border-t-transparent"></div>
            <span id="upload-status" class="text-sm font-medium text-violet-700 dark:text-violet-300">Subiendo archivos...</span>
        </div>
        <div class="h-1.5 w-full rounded-full bg-violet-100 dark:bg-violet-900/30">
            <div id="upload-bar" class="h-1.5 rounded-full bg-violet-600 transition-all duration-300" style="width:0%"></div>
        </div>
    </div>

    <div class="rounded-xl border {{ $pct >= 100 ? 'border-red-200 bg-red-50 dark:border-red-700/50 dark:bg-red-900/10' : ($pct >= 80 ? 'border-amber-200 bg-amber-50 dark:border-amber-700/50 dark:bg-amber-900/10' : 'border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900') }} p-4 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="min-w-0 flex-1">
                <div class="mb-2 flex items-center gap-2">
                    <i class="fas fa-hard-drive text-sm {{ $pct >= 100 ? 'text-red-500' : ($pct >= 80 ? 'text-amber-500' : 'text-violet-500') }}"></i>
                    <span class="text-sm font-semibold text-zinc-800 dark:text-zinc-100">Plan: {{ $storagePlan?->name ?? 'Sin plan' }}</span>
                </div>
                <div class="flex items-center gap-3">
                    <div class="h-2 flex-1 overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800">
                        <div class="h-2 rounded-full {{ $pct >= 100 ? 'bg-red-500' : ($pct >= 80 ? 'bg-amber-500' : 'bg-violet-500') }}" style="width: {{ $pct }}%"></div>
                    </div>
                    <span class="shrink-0 font-mono text-xs text-zinc-500">
                        {{ $fmtBytes($usedBytes) }} @if($limitBytes > 0)/ {{ $fmtBytes($limitBytes) }} ({{ $pct }}%)@endif
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-3 sm:grid-cols-5">
        @foreach([
            ['label' => 'Total', 'value' => $totals['count'], 'icon' => 'fa-layer-group', 'class' => 'bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300'],
            ['label' => 'Espacio', 'value' => $fmtBytes($totals['size']), 'icon' => 'fa-database', 'class' => 'bg-violet-100 text-violet-600 dark:bg-violet-900/30 dark:text-violet-300'],
            ['label' => 'Imágenes', 'value' => $totals['images'], 'icon' => 'fa-image', 'class' => 'bg-violet-100 text-violet-600 dark:bg-violet-900/30 dark:text-violet-300'],
            ['label' => 'Videos', 'value' => $totals['videos'], 'icon' => 'fa-film', 'class' => 'bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-300'],
            ['label' => 'Documentos', 'value' => $totals['documents'], 'icon' => 'fa-file-lines', 'class' => 'bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-300'],
        ] as $stat)
            <div class="flex items-center gap-3 rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex size-9 shrink-0 items-center justify-center rounded-lg {{ $stat['class'] }}">
                    <i class="fas {{ $stat['icon'] }} text-sm"></i>
                </div>
                <div class="min-w-0">
                    <div class="truncate text-lg font-bold leading-none text-zinc-800 dark:text-zinc-100">{{ $stat['value'] }}</div>
                    <div class="mt-0.5 truncate text-[11px] text-zinc-500">{{ $stat['label'] }}</div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <form method="GET" action="{{ route('admin.media.index') }}" class="flex flex-wrap items-center gap-2">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Buscar por nombre..."
                       class="h-9 w-52 rounded-lg border border-zinc-200 bg-white px-3 text-sm text-zinc-800 focus:border-violet-400 focus:outline-none focus:ring-2 focus:ring-violet-400/20 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200">

                <select name="type" class="h-9 rounded-lg border border-zinc-200 bg-white px-3 text-sm text-zinc-700 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                    <option value="">Todos los tipos</option>
                    <option value="image" @selected(request('type') === 'image')>Imágenes</option>
                    <option value="video" @selected(request('type') === 'video')>Videos</option>
                    <option value="application" @selected(request('type') === 'application')>Documentos</option>
                </select>

                <select name="tag" class="h-9 rounded-lg border border-zinc-200 bg-white px-3 text-sm text-zinc-700 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                    <option value="">Todos los tags</option>
                    @foreach($tags as $tag)
                        <option value="{{ $tag->slug }}" @selected(request('tag') === $tag->slug)>{{ $tag->name }}</option>
                    @endforeach
                </select>

                <flux:button type="submit" variant="ghost" size="sm">Filtrar</flux:button>
                @if(request()->hasAny(['q', 'type', 'tag', 'album']))
                    <a href="{{ route('admin.media.index') }}" class="inline-flex h-9 items-center gap-1 rounded-lg px-3 text-sm text-zinc-500 transition hover:text-zinc-800 dark:hover:text-zinc-200">
                        <i class="fas fa-xmark text-xs"></i> Limpiar
                    </a>
                @endif
            </form>

            <div class="flex items-center gap-2 rounded-lg border border-zinc-200 p-1 dark:border-zinc-700">
                <button type="button" @click="viewMode = 'grid'" :class="viewMode === 'grid' ? 'bg-zinc-900 text-white dark:bg-white dark:text-zinc-900' : 'text-zinc-500'" class="flex size-8 items-center justify-center rounded-md transition" title="Grilla">
                    <i class="fas fa-grip text-xs"></i>
                </button>
                <button type="button" @click="viewMode = 'list'" :class="viewMode === 'list' ? 'bg-zinc-900 text-white dark:bg-white dark:text-zinc-900' : 'text-zinc-500'" class="flex size-8 items-center justify-center rounded-md transition" title="Lista">
                    <i class="fas fa-list text-xs"></i>
                </button>
            </div>
        </div>

        <div x-show="selected.length > 0" x-cloak class="mt-4 rounded-lg border border-violet-200 bg-violet-50 p-3 dark:border-violet-700/50 dark:bg-violet-900/10">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <span class="text-sm font-semibold text-violet-700 dark:text-violet-300" x-text="selected.length + ' seleccionado(s)'"></span>
                <div class="flex flex-wrap gap-2">
                    <form method="POST" action="{{ route('admin.media.bulk-attach') }}" x-ref="bulkAttachForm" class="flex gap-2">
                        @csrf
                        <template x-for="id in selected" :key="'attach-' + id">
                            <input type="hidden" name="media_ids[]" :value="id">
                        </template>
                        <select name="album_id" required class="h-9 rounded-lg border border-zinc-200 bg-white px-3 text-sm dark:border-zinc-700 dark:bg-zinc-800">
                            <option value="">Enviar a álbum</option>
                            @foreach($albums as $album)
                                <option value="{{ $album->id }}">{{ $album->name }}</option>
                            @endforeach
                        </select>
                        <button type="button" @click="confirmSubmit($refs.bulkAttachForm, 'Agregar a álbum', '¿Agregar los archivos seleccionados al álbum?')" class="h-9 rounded-lg bg-violet-600 px-4 text-sm font-semibold text-white hover:bg-violet-700">Agregar</button>
                    </form>

                    <form method="POST" action="{{ route('admin.media.bulk-delete') }}" x-ref="bulkDeleteForm">
                        @csrf
                        <template x-for="id in selected" :key="'delete-' + id">
                            <input type="hidden" name="media_ids[]" :value="id">
                        </template>
                        <button type="button" @click="confirmSubmit($refs.bulkDeleteForm, 'Eliminar selección', '¿Eliminar en cascada los archivos seleccionados del storage? Esta acción no se puede deshacer.')" class="h-9 rounded-lg bg-rose-600 px-4 text-sm font-semibold text-white hover:bg-rose-700">Eliminar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @if($media->isEmpty())
        <div class="py-24 text-center text-zinc-400 dark:text-zinc-600">
            <i class="fas fa-photo-film mb-4 block text-5xl opacity-40"></i>
            <p class="text-sm font-medium">No hay archivos multimedia</p>
            <p class="mt-1 text-xs">Sube archivos o entra al dashboard de álbumes para organizarlos.</p>
        </div>
    @else
        <div x-show="viewMode === 'grid'" class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6">
            @foreach($media as $item)
                @php
                    $ftype = $item->fileType();
                    $icon = $typeIcons[$ftype] ?? 'fa-file';
                    $badge = $typeBadge[$ftype] ?? 'bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300';
                @endphp
                <article class="group relative rounded-xl border border-zinc-200 bg-white shadow-sm transition duration-300 hover:z-20 hover:-translate-y-1 hover:border-zinc-300 hover:shadow-2xl dark:border-zinc-700 dark:bg-zinc-900 dark:hover:border-zinc-500">
                    <div
                        class="relative aspect-square overflow-hidden rounded-t-xl bg-zinc-950 {{ $item->isImage() || $item->isVideo() ? 'cursor-zoom-in' : '' }}"
                        @if($item->isImage() || $item->isVideo()) @click="openViewerById({{ $item->id }})" @endif
                    >
                        <label
                            class="absolute left-2 top-2 z-10 grid size-8 cursor-pointer place-items-center rounded-lg bg-white/95 text-zinc-900 shadow ring-1 ring-black/10 transition dark:bg-zinc-900/95 dark:text-white dark:ring-white/15"
                            :class="selected.includes({{ $item->id }}) ? 'opacity-100' : 'opacity-0 group-hover:opacity-100'"
                            @click.stop
                            title="Seleccionar"
                        >
                            <input type="checkbox" class="peer sr-only" value="{{ $item->id }}" @change="toggle({{ $item->id }}, $event.target.checked)" :checked="selected.includes({{ $item->id }})">
                            <span class="grid size-4 place-items-center rounded border border-zinc-400 bg-white text-transparent transition peer-checked:border-violet-600 peer-checked:bg-violet-600 peer-checked:text-white dark:bg-zinc-800">
                                <flux:icon.check class="size-3" />
                            </span>
                        </label>

                        @if($item->isImage())
                            <img src="{{ $item->public_url }}" alt="{{ $item->alt ?? $item->name }}" class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105" loading="lazy">
                        @elseif($item->isVideo())
                            <video src="{{ $item->public_url }}" class="h-full w-full object-cover" preload="metadata"></video>
                        @else
                            <div class="flex h-full w-full flex-col items-center justify-center gap-2 {{ $badge }}">
                                <i class="fas {{ $icon }} text-3xl"></i>
                            </div>
                        @endif

                        <span class="absolute right-2 top-2 z-10 inline-flex items-center gap-1 rounded-full bg-black/60 px-1.5 py-0.5 text-[9px] font-semibold uppercase text-white shadow-sm backdrop-blur">
                            <i class="fas {{ $icon }} text-[8px]"></i>{{ $ftype }}
                        </span>

                        <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/20 to-transparent opacity-70 transition-opacity duration-300 group-hover:opacity-100"></div>
                        <div class="absolute bottom-3 left-2 right-2 transition-all duration-300 group-hover:bottom-4">
                            <p class="mb-2 truncate text-sm font-bold text-white drop-shadow" title="{{ $item->name }}">{{ $item->name }}</p>
                            <div class="flex flex-wrap items-center justify-center gap-1 rounded-2xl bg-black/45 p-1 shadow-xl ring-1 ring-white/10 backdrop-blur" @click.stop>
                            @if($item->isImage() || $item->isVideo())
                                <button type="button" @click="openViewerById({{ $item->id }})" class="flex size-8 scale-100 items-center justify-center rounded-full bg-white text-zinc-950 shadow-lg ring-1 ring-black/10 transition hover:scale-110" title="Ver">
                                    <i class="fas {{ $item->isVideo() ? 'fa-play' : 'fa-eye' }} text-sm"></i>
                                </button>
                            @else
                                <a href="{{ $item->public_url }}" target="_blank" rel="noopener" class="flex size-8 scale-100 items-center justify-center rounded-full bg-white text-zinc-950 shadow-lg transition hover:scale-110" title="Abrir">
                                    <i class="fas fa-arrow-up-right-from-square text-xs"></i>
                                </a>
                            @endif
                            <a href="{{ route('admin.media.download', $item) }}" class="flex size-8 items-center justify-center rounded-full bg-zinc-900/75 text-white ring-1 ring-white/25 backdrop-blur transition hover:bg-white hover:text-zinc-950" title="Descargar">
                                <i class="fas fa-download text-xs"></i>
                            </a>
                            <button type="button" onclick="copyUrl('{{ addslashes($item->public_url) }}')" class="flex size-8 items-center justify-center rounded-full bg-zinc-900/75 text-white ring-1 ring-white/25 backdrop-blur transition hover:bg-white hover:text-zinc-950" title="Copiar URL">
                                <i class="fas fa-copy text-xs"></i>
                            </button>
                            <form method="POST" action="{{ route('admin.media.destroy', $item) }}" data-confirm-title="Eliminar archivo" data-confirm-message="¿Eliminar {{ e($item->name) }} del storage? Esta acción no se puede deshacer.">
                                @csrf @method('DELETE')
                                <button type="submit" class="flex size-8 items-center justify-center rounded-full bg-rose-600/90 text-white ring-1 ring-white/20 backdrop-blur transition hover:bg-rose-500" title="Eliminar">
                                    <i class="fas fa-trash text-xs"></i>
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
                        @if($item->albums->isNotEmpty())
                            <p class="truncate text-[10px] text-emerald-600 dark:text-emerald-400"><i class="fas fa-folder text-[8px]"></i> {{ $item->albums->pluck('name')->implode(', ') }}</p>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>

        <div x-show="viewMode === 'list'" x-cloak class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="grid grid-cols-[44px_1fr_130px_120px_170px] gap-3 border-b border-zinc-200 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-zinc-400 dark:border-zinc-700">
                <span></span>
                <span>Archivo</span>
                <span>Tipo</span>
                <span>Tamaño</span>
                <span class="text-right">Acciones</span>
            </div>
            @foreach($media as $item)
                @php
                    $ftype = $item->fileType();
                @endphp
                <div
                    class="grid grid-cols-[44px_1fr_130px_120px_170px] items-center gap-3 border-b border-zinc-100 px-4 py-3 last:border-b-0 dark:border-zinc-800 {{ $item->isImage() || $item->isVideo() ? 'cursor-zoom-in hover:bg-zinc-50 dark:hover:bg-zinc-800/70' : '' }}"
                    @if($item->isImage() || $item->isVideo()) @click="openViewerById({{ $item->id }})" @endif
                >
                    <label class="grid size-8 cursor-pointer place-items-center rounded-lg bg-zinc-50 dark:bg-zinc-800" @click.stop title="Seleccionar">
                        <input type="checkbox" class="peer sr-only" value="{{ $item->id }}" @change="toggle({{ $item->id }}, $event.target.checked)" :checked="selected.includes({{ $item->id }})">
                        <span class="grid size-4 place-items-center rounded border border-zinc-300 bg-white text-transparent transition peer-checked:border-violet-600 peer-checked:bg-violet-600 peer-checked:text-white dark:border-zinc-600 dark:bg-zinc-900">
                            <flux:icon.check class="size-3" />
                        </span>
                    </label>
                    <div class="min-w-0">
                        <p class="truncate text-sm font-medium text-zinc-800 dark:text-zinc-100">{{ $item->name }}</p>
                        <p class="truncate text-xs text-zinc-500">{{ $item->albums->isNotEmpty() ? $item->albums->pluck('name')->implode(', ') : 'Sin álbum' }}</p>
                    </div>
                    <span class="text-sm text-zinc-500">{{ $ftype }}</span>
                    <span class="text-sm text-zinc-500">{{ $item->sizeLabel() }}</span>
                    <div class="flex justify-end gap-1" @click.stop>
                        @if($item->isImage() || $item->isVideo())
                            <button type="button" @click="openViewerById({{ $item->id }})" class="flex size-8 items-center justify-center rounded-lg border border-zinc-200 text-zinc-600 hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-300 dark:hover:bg-zinc-800" title="Ver">
                                <i class="fas fa-eye text-xs"></i>
                            </button>
                        @endif
                        <a href="{{ route('admin.media.download', $item) }}" class="flex size-8 items-center justify-center rounded-lg border border-zinc-200 text-zinc-600 hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-300 dark:hover:bg-zinc-800" title="Descargar"><i class="fas fa-download text-xs"></i></a>
                        <form method="POST" action="{{ route('admin.media.destroy', $item) }}" data-confirm-title="Eliminar archivo" data-confirm-message="¿Eliminar {{ e($item->name) }} del storage? Esta acción no se puede deshacer.">
                            @csrf @method('DELETE')
                            <button class="flex size-8 items-center justify-center rounded-lg border border-rose-200 text-rose-600 hover:bg-rose-50 dark:border-rose-700 dark:hover:bg-rose-900/20" title="Eliminar"><i class="fas fa-trash text-xs"></i></button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>

        @if($media->hasPages())
            <div>{{ $media->links() }}</div>
        @endif
    @endif

    <livewire:admin.media-viewer />

    <div id="copy-toast" class="fixed bottom-6 right-6 z-50 hidden items-center gap-2 rounded-xl bg-zinc-900 px-4 py-2.5 text-sm text-white shadow-lg">
        <i class="fas fa-check text-xs text-emerald-400"></i>
        URL copiada al portapapeles
    </div>
</div>

<script>
function mediaLibrary() {
    return {
        viewMode: localStorage.getItem('adminMediaViewMode') || 'grid',
        selected: [],
        viewerIds: @js($viewerIds),
        toggle(id, checked) {
            id = Number(id);
            this.selected = checked
                ? [...new Set([...this.selected, id])]
                : this.selected.filter(item => item !== id);
        },
        openViewerById(id) {
            Livewire.dispatch('openAdminMediaViewer', { id: Number(id), ids: this.viewerIds });
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
            this.$watch('viewMode', value => localStorage.setItem('adminMediaViewMode', value));
        },
    };
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

function copyUrl(url) {
    navigator.clipboard.writeText(url).then(() => {
        const t = document.getElementById('copy-toast');
        t.classList.remove('hidden');
        t.classList.add('flex');
        setTimeout(() => {
            t.classList.add('hidden');
            t.classList.remove('flex');
        }, 2200);
    });
}

async function bulkUpload(files) {
    if (!files.length) return;
    const area = document.getElementById('upload-area');
    const bar = document.getElementById('upload-bar');
    const status = document.getElementById('upload-status');
    area.classList.remove('hidden');

    let done = 0;
    for (const file of files) {
        status.textContent = `Subiendo ${file.name}...`;
        const fd = new FormData();
        fd.append('file', file);
        fd.append('_token', '{{ csrf_token() }}');

        try {
            const res = await fetch('{{ route("admin.media.upload") }}', { method: 'POST', body: fd });
            const data = await res.json();
            if (!data.success) window.Starcho.notify('error', data.message || 'No se pudo subir el archivo.');
        } catch (err) {
            window.Starcho.notify('error', 'No se pudo subir ' + file.name);
        }

        done++;
        bar.style.width = Math.round(done / files.length * 100) + '%';
    }

    status.textContent = 'Completado. Recargando...';
    setTimeout(() => location.reload(), 800);
}
</script>

</x-layouts::admin>
