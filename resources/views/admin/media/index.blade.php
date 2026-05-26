<x-layouts::admin title="Galería Multimedia">

<style>
.media-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:12px}
.media-card{position:relative;border-radius:12px;overflow:hidden;border:1px solid;transition:box-shadow .15s,border-color .15s}
.media-card:hover .media-overlay{opacity:1}
.media-overlay{position:absolute;inset:0;background:rgba(0,0,0,.55);opacity:0;transition:opacity .15s;display:flex;align-items:flex-end;padding:8px;gap:6px}
.media-thumb{width:100%;aspect-ratio:1;object-fit:cover;display:block;background:#f4f4f5}
.dark .media-thumb{background:#27272a}
.media-file-icon{width:100%;aspect-ratio:1;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:6px;background:#f4f4f5}
.dark .media-file-icon{background:#27272a}
</style>

<div class="flex items-center gap-3 mb-6">
    <flux:heading size="xl" level="1">Galería Multimedia</flux:heading>
    <div class="flex-1"></div>
    {{-- Upload button --}}
    <label for="bulk-upload" class="inline-flex items-center gap-2 h-9 px-4 rounded-lg bg-violet-600 hover:bg-violet-700 text-white text-sm font-medium cursor-pointer transition shadow-sm shadow-violet-500/25">
        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5"/>
        </svg>
        Subir archivos
    </label>
    <input type="file" id="bulk-upload" class="hidden" multiple
           accept="image/*,application/pdf,.doc,.docx,.xls,.xlsx,.zip"
           onchange="bulkUpload(this.files)">
</div>

@include('admin.partials.alerts')

{{-- Upload progress area --}}
<div id="upload-area" class="hidden mb-4 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-4">
    <div class="flex items-center gap-3 mb-2">
        <div id="upload-spinner" class="size-4 border-2 border-violet-600 border-t-transparent rounded-full animate-spin"></div>
        <span id="upload-status" class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Subiendo archivos…</span>
    </div>
    <div class="w-full bg-zinc-100 dark:bg-zinc-800 rounded-full h-1.5">
        <div id="upload-bar" class="bg-violet-600 h-1.5 rounded-full transition-all" style="width:0%"></div>
    </div>
</div>

{{-- Storage totals --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
    @php
        $totalLabel = function(int $bytes): string {
            if ($bytes >= 1_073_741_824) return number_format($bytes / 1_073_741_824, 2) . ' GB';
            if ($bytes >= 1_048_576)     return number_format($bytes / 1_048_576, 2)    . ' MB';
            if ($bytes >= 1024)          return number_format($bytes / 1024, 1)         . ' KB';
            return $bytes . ' B';
        };
    @endphp
    <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-4">
        <div class="text-2xl font-bold text-zinc-800 dark:text-zinc-100">{{ $totals['count'] }}</div>
        <div class="text-xs text-zinc-500 mt-0.5">Total archivos</div>
    </div>
    <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-4">
        <div class="text-2xl font-bold text-zinc-800 dark:text-zinc-100">{{ $totalLabel($totals['size']) }}</div>
        <div class="text-xs text-zinc-500 mt-0.5">Espacio usado</div>
    </div>
    <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-4">
        <div class="text-2xl font-bold text-violet-600 dark:text-violet-400">{{ $totals['images'] }}</div>
        <div class="text-xs text-zinc-500 mt-0.5">Imágenes</div>
    </div>
    <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-4">
        <div class="text-2xl font-bold text-zinc-600 dark:text-zinc-400">{{ $totals['documents'] }}</div>
        <div class="text-xs text-zinc-500 mt-0.5">Otros archivos</div>
    </div>
</div>

{{-- Filters --}}
<form method="GET" action="{{ route('admin.media.index') }}"
      class="flex flex-wrap items-center gap-2 mb-5">
    <input type="text" name="q" value="{{ request('q') }}"
           placeholder="Buscar por nombre…"
           class="h-9 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800
                  text-sm text-zinc-800 dark:text-zinc-200 px-3 focus:outline-none focus:ring-2
                  focus:ring-violet-400/20 focus:border-violet-400 dark:focus:border-violet-500 w-52 transition">

    <select name="type"
            class="h-9 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800
                   text-sm text-zinc-700 dark:text-zinc-300 px-3 pr-8 focus:outline-none focus:ring-2
                   focus:ring-violet-400/20 focus:border-violet-400 transition">
        <option value="">Todos los tipos</option>
        <option value="image" {{ request('type') === 'image' ? 'selected' : '' }}>Imágenes</option>
        <option value="application" {{ request('type') === 'application' ? 'selected' : '' }}>Documentos</option>
    </select>

    <select name="context"
            class="h-9 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800
                   text-sm text-zinc-700 dark:text-zinc-300 px-3 pr-8 focus:outline-none focus:ring-2
                   focus:ring-violet-400/20 focus:border-violet-400 transition">
        <option value="">Todos los contextos</option>
        <option value="gallery"        {{ request('context') === 'gallery' ? 'selected' : '' }}>Galería</option>
        <option value="featured_image" {{ request('context') === 'featured_image' ? 'selected' : '' }}>Imagen destacada</option>
        <option value="editor"         {{ request('context') === 'editor' ? 'selected' : '' }}>Editor</option>
    </select>

    <flux:button type="submit" variant="ghost" size="sm">Filtrar</flux:button>

    @if(request()->hasAny(['q', 'type', 'context']))
        <a href="{{ route('admin.media.index') }}"
           class="h-9 px-3 rounded-lg text-sm text-zinc-500 hover:text-zinc-800 dark:hover:text-zinc-200
                  inline-flex items-center gap-1 transition">
            <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
            </svg>
            Limpiar
        </a>
    @endif
</form>

{{-- Grid --}}
@if($media->isEmpty())
    <div class="text-center py-20 text-zinc-400 dark:text-zinc-600">
        <svg class="size-16 mx-auto mb-4 opacity-40" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/>
        </svg>
        <p class="text-sm font-medium">No hay archivos multimedia</p>
        <p class="text-xs mt-1">Sube archivos usando el botón de arriba</p>
    </div>
@else
    <div class="media-grid" id="media-grid">
        @foreach($media as $item)
            @php
                $parentLabel = null;
                if ($item->mediable) {
                    $parentLabel = match(true) {
                        $item->mediable instanceof \App\Models\Post => '[' . strtoupper($item->mediable->type) . '] ' . ($item->mediable->getTranslation('title', app()->getLocale(), false) ?: ($item->mediable->getRawOriginal('title') ?? 'Sin título')),
                        default => class_basename($item->mediable_type) . ' #' . $item->mediable_id,
                    };
                }
            @endphp
            <div class="media-card border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900"
                 data-id="{{ $item->id }}">
                {{-- Thumbnail --}}
                @if($item->isImage())
                    <img src="{{ $item->public_url }}"
                         alt="{{ $item->alt ?? $item->original_name }}"
                         class="media-thumb"
                         loading="lazy"
                         onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22%23a1a1aa%22 stroke-width=%221%22%3E%3Crect x=%222%22 y=%222%22 width=%2220%22 height=%2220%22 rx=%222%22/%3E%3Ccircle cx=%228.5%22 cy=%228.5%22 r=%221.5%22/%3E%3Cpath d=%22m21 15-5-5L5 21%22/%3E%3C/svg%3E'">
                @else
                    <div class="media-file-icon">
                        <svg class="size-10 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke-width="1.2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/>
                        </svg>
                        <span class="text-xs font-mono text-zinc-500 uppercase">
                            {{ pathinfo($item->original_name, PATHINFO_EXTENSION) ?: 'file' }}
                        </span>
                    </div>
                @endif

                {{-- Hover overlay actions --}}
                <div class="media-overlay">
                    <a href="{{ $item->public_url }}" target="_blank" rel="noopener"
                       class="flex-1 inline-flex items-center justify-center h-8 rounded-lg bg-white/90 text-zinc-800 text-xs font-medium hover:bg-white transition"
                       title="Ver archivo">
                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/>
                        </svg>
                    </a>
                    <form method="POST" action="{{ route('admin.media.destroy', $item) }}"
                          onsubmit="return confirmDelete(event, '{{ addslashes($item->original_name) }}')"
                          class="flex-1">
                        @csrf @method('DELETE')
                        <button type="submit"
                                class="w-full h-8 rounded-lg bg-rose-500/90 hover:bg-rose-600 text-white text-xs font-medium transition inline-flex items-center justify-center"
                                title="Eliminar">
                            <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/>
                            </svg>
                        </button>
                    </form>
                </div>

                {{-- Footer info --}}
                <div class="px-2.5 py-2 border-t border-zinc-100 dark:border-zinc-800">
                    <p class="text-xs font-medium text-zinc-700 dark:text-zinc-300 truncate" title="{{ $item->original_name }}">
                        {{ $item->original_name }}
                    </p>
                    <div class="flex items-center gap-1.5 mt-0.5 flex-wrap">
                        <span class="text-[10px] text-zinc-400">{{ $item->sizeLabel() }}</span>
                        @if($item->width && $item->height)
                            <span class="text-[10px] text-zinc-300 dark:text-zinc-600">·</span>
                            <span class="text-[10px] text-zinc-400">{{ $item->width }}×{{ $item->height }}</span>
                        @endif
                    </div>
                    @if($parentLabel)
                        <p class="text-[10px] text-violet-600 dark:text-violet-400 mt-0.5 truncate" title="{{ $parentLabel }}">
                            {{ $parentLabel }}
                        </p>
                    @else
                        <p class="text-[10px] text-zinc-400 mt-0.5">Sin asignar</p>
                    @endif
                    <p class="text-[10px] text-zinc-400 mt-0.5">{{ $item->created_at->format('d/m/Y') }}</p>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Pagination --}}
    @if($media->hasPages())
        <div class="mt-6">
            {{ $media->links() }}
        </div>
    @endif
@endif

<script>
function confirmDelete(e, name) {
    if (!confirm('¿Eliminar "' + name + '"?\nEsta acción no se puede deshacer.')) {
        e.preventDefault();
        return false;
    }
    return true;
}

async function bulkUpload(files) {
    if (!files.length) return;

    const area   = document.getElementById('upload-area');
    const bar    = document.getElementById('upload-bar');
    const status = document.getElementById('upload-status');
    area.classList.remove('hidden');

    let done = 0;
    const total = files.length;

    for (const file of files) {
        status.textContent = `Subiendo ${file.name}…`;
        const fd = new FormData();
        fd.append('file', file);
        fd.append('_token', '{{ csrf_token() }}');

        try {
            const res = await fetch('{{ route("admin.media.upload") }}', { method: 'POST', body: fd });
            const data = await res.json();
            if (!data.success) {
                console.warn('Error uploading', file.name, data.message);
            }
        } catch (err) {
            console.error('Upload failed', file.name, err);
        }

        done++;
        bar.style.width = Math.round(done / total * 100) + '%';
    }

    status.textContent = 'Completado. Recargando…';
    setTimeout(() => location.reload(), 800);
}
</script>

</x-layouts::admin>
