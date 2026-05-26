<x-layouts::admin title="Galería Multimedia">

@php
    $fmtBytes = function (int $bytes): string {
        if ($bytes >= 1_073_741_824) return number_format($bytes / 1_073_741_824, 2) . ' GB';
        if ($bytes >= 1_048_576)     return number_format($bytes / 1_048_576, 2)    . ' MB';
        if ($bytes >= 1024)          return number_format($bytes / 1024, 1)         . ' KB';
        return $bytes . ' B';
    };

    $typeIcons = [
        'image'    => 'fa-image',
        'video'    => 'fa-film',
        'document' => 'fa-file-lines',
    ];
    $typeColors = [
        'image'    => 'text-violet-500',
        'video'    => 'text-blue-500',
        'document' => 'text-amber-500',
    ];
    $typeBg = [
        'image'    => 'bg-violet-100 dark:bg-violet-900/30',
        'video'    => 'bg-blue-100 dark:bg-blue-900/30',
        'document' => 'bg-amber-100 dark:bg-amber-900/30',
    ];
@endphp

{{-- ── Page header ─────────────────────────────────────────────────── --}}
<div class="flex items-start justify-between gap-4 mb-6">
    <div>
        <flux:heading size="xl" level="1" class="mb-0.5">Galería Multimedia</flux:heading>
        <flux:text class="text-zinc-500 text-sm">
            Driver activo:
            <span class="font-semibold text-zinc-700 dark:text-zinc-300">{{ ucfirst($storageSetting->default_driver) }}</span>
            @if($storageSetting->isLocal() && filled($storageSetting->local_url))
                · Base URL: <code class="font-mono text-xs bg-zinc-100 dark:bg-zinc-800 px-1.5 py-0.5 rounded">{{ $storageSetting->local_url }}</code>
            @endif
        </flux:text>
    </div>
    <label for="bulk-upload"
           class="inline-flex items-center gap-2 h-9 px-4 rounded-lg bg-violet-600 hover:bg-violet-700 text-white text-sm font-medium cursor-pointer transition shadow-sm shadow-violet-500/25 shrink-0">
        <i class="fas fa-upload text-xs"></i>
        Subir archivos
    </label>
    <input type="file" id="bulk-upload" class="hidden" multiple
           accept="image/*,video/*,application/pdf,.doc,.docx,.xls,.xlsx,.zip"
           onchange="bulkUpload(this.files)">
</div>

@include('admin.partials.alerts')

{{-- ── Upload progress ──────────────────────────────────────────────── --}}
<div id="upload-area" class="hidden mb-5 rounded-xl border border-violet-200 dark:border-violet-700/50 bg-violet-50 dark:bg-violet-900/10 p-4">
    <div class="flex items-center gap-3 mb-2">
        <div class="size-4 border-2 border-violet-600 border-t-transparent rounded-full animate-spin"></div>
        <span id="upload-status" class="text-sm font-medium text-violet-700 dark:text-violet-300">Subiendo archivos…</span>
    </div>
    <div class="w-full bg-violet-100 dark:bg-violet-900/30 rounded-full h-1.5">
        <div id="upload-bar" class="bg-violet-600 h-1.5 rounded-full transition-all duration-300" style="width:0%"></div>
    </div>
</div>

{{-- ── Plan / quota banner ──────────────────────────────────────────── --}}
<div class="rounded-xl border {{ $pct >= 100 ? 'border-red-200 dark:border-red-700/50 bg-red-50 dark:bg-red-900/10' : ($pct >= 80 ? 'border-amber-200 dark:border-amber-700/50 bg-amber-50 dark:bg-amber-900/10' : 'border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900') }} p-4 mb-5 shadow-sm">
    <div class="flex items-start justify-between gap-4 flex-wrap">
        {{-- Left: plan info + bar --}}
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 mb-1.5">
                <i class="fas fa-hard-drive text-sm {{ $pct >= 100 ? 'text-red-500' : ($pct >= 80 ? 'text-amber-500' : 'text-violet-500') }}"></i>
                <span class="text-sm font-semibold text-zinc-800 dark:text-zinc-100">
                    Plan: {{ $storagePlan?->name ?? 'Sin plan asignado' }}
                </span>
                @if($storagePlan?->is_free)
                    <span class="inline-flex items-center rounded-full bg-zinc-100 dark:bg-zinc-800 px-2 py-0.5 text-[10px] font-medium text-zinc-500 uppercase tracking-wide">Gratuito</span>
                @endif
                @if($pct >= 100)
                    <span class="inline-flex items-center gap-1 rounded-full bg-red-100 dark:bg-red-900/30 px-2 py-0.5 text-[10px] font-semibold text-red-600 dark:text-red-400 uppercase tracking-wide">
                        <i class="fas fa-triangle-exclamation text-[9px]"></i> Límite alcanzado
                    </span>
                @elseif($pct >= 80)
                    <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 dark:bg-amber-900/30 px-2 py-0.5 text-[10px] font-semibold text-amber-600 dark:text-amber-400 uppercase tracking-wide">
                        <i class="fas fa-triangle-exclamation text-[9px]"></i> Casi lleno
                    </span>
                @endif
            </div>

            {{-- Progress bar --}}
            <div class="flex items-center gap-3">
                <div class="flex-1 bg-zinc-100 dark:bg-zinc-800 rounded-full h-2 overflow-hidden">
                    <div class="h-2 rounded-full transition-all duration-500
                        {{ $pct >= 100 ? 'bg-red-500' : ($pct >= 80 ? 'bg-amber-500' : 'bg-violet-500') }}"
                         style="width: {{ $pct }}%"></div>
                </div>
                <span class="text-xs font-mono text-zinc-500 dark:text-zinc-400 shrink-0">
                    {{ $fmtBytes($usedBytes) }}
                    @if($limitBytes > 0)
                        / {{ $fmtBytes($limitBytes) }}
                    @endif
                    @if($limitBytes > 0)
                        <span class="text-zinc-400">({{ $pct }}%)</span>
                    @endif
                </span>
            </div>
        </div>

        {{-- Right: upgrade CTA --}}
        @if($pct >= 80 && $upgradePlan)
        <div class="shrink-0">
            <a href="{{ route('admin.storage.index') }}"
               class="inline-flex items-center gap-2 h-9 px-4 rounded-lg
                      {{ $pct >= 100 ? 'bg-red-600 hover:bg-red-700' : 'bg-amber-500 hover:bg-amber-600' }}
                      text-white text-sm font-semibold transition shadow-sm">
                <i class="fas fa-arrow-up text-xs"></i>
                Mejorar a {{ $upgradePlan->name }} ({{ $upgradePlan->limitLabel() }})
            </a>
        </div>
        @endif
    </div>
</div>

{{-- ── Stats cards ──────────────────────────────────────────────────── --}}
<div class="grid grid-cols-2 sm:grid-cols-5 gap-3 mb-5">
    @foreach([
        ['label' => 'Total archivos', 'value' => $totals['count'],     'icon' => 'fa-layer-group',  'color' => 'text-zinc-600 dark:text-zinc-300',    'bg' => 'bg-zinc-100 dark:bg-zinc-800'],
        ['label' => 'Espacio usado',  'value' => $fmtBytes($totals['size']), 'icon' => 'fa-database', 'color' => 'text-violet-600 dark:text-violet-400', 'bg' => 'bg-violet-100 dark:bg-violet-900/30'],
        ['label' => 'Imágenes',       'value' => $totals['images'],    'icon' => 'fa-image',        'color' => 'text-violet-500 dark:text-violet-400',  'bg' => 'bg-violet-100 dark:bg-violet-900/30'],
        ['label' => 'Videos',         'value' => $totals['videos'],    'icon' => 'fa-film',         'color' => 'text-blue-500 dark:text-blue-400',      'bg' => 'bg-blue-100 dark:bg-blue-900/30'],
        ['label' => 'Documentos',     'value' => $totals['documents'], 'icon' => 'fa-file-lines',   'color' => 'text-amber-500 dark:text-amber-400',    'bg' => 'bg-amber-100 dark:bg-amber-900/30'],
    ] as $stat)
    <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-4 flex items-center gap-3 shadow-sm">
        <div class="size-9 rounded-lg {{ $stat['bg'] }} flex items-center justify-center shrink-0">
            <i class="fas {{ $stat['icon'] }} text-sm {{ $stat['color'] }}"></i>
        </div>
        <div class="min-w-0">
            <div class="text-lg font-bold text-zinc-800 dark:text-zinc-100 leading-none">{{ $stat['value'] }}</div>
            <div class="text-[11px] text-zinc-500 mt-0.5 truncate">{{ $stat['label'] }}</div>
        </div>
    </div>
    @endforeach
</div>

{{-- ── Filters ───────────────────────────────────────────────────────── --}}
<form method="GET" action="{{ route('admin.media.index') }}"
      class="flex flex-wrap items-center gap-2 mb-5">
    <input type="text" name="q" value="{{ request('q') }}"
           placeholder="Buscar por nombre…"
           class="h-9 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800
                  text-sm text-zinc-800 dark:text-zinc-200 px-3 focus:outline-none focus:ring-2
                  focus:ring-violet-400/20 focus:border-violet-400 w-48 transition">

    <select name="type"
            class="h-9 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800
                   text-sm text-zinc-700 dark:text-zinc-300 px-3 focus:outline-none focus:ring-2
                   focus:ring-violet-400/20 focus:border-violet-400 transition">
        <option value="">Todos los tipos</option>
        <option value="image" {{ request('type') === 'image' ? 'selected' : '' }}>Imágenes</option>
        <option value="video" {{ request('type') === 'video' ? 'selected' : '' }}>Videos</option>
        <option value="application" {{ request('type') === 'application' ? 'selected' : '' }}>Documentos</option>
    </select>

    <select name="context"
            class="h-9 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800
                   text-sm text-zinc-700 dark:text-zinc-300 px-3 focus:outline-none focus:ring-2
                   focus:ring-violet-400/20 focus:border-violet-400 transition">
        <option value="">Todos los contextos</option>
        <option value="gallery"        {{ request('context') === 'gallery'        ? 'selected' : '' }}>Galería</option>
        <option value="featured_image" {{ request('context') === 'featured_image' ? 'selected' : '' }}>Imagen destacada</option>
        <option value="editor"         {{ request('context') === 'editor'         ? 'selected' : '' }}>Editor</option>
    </select>

    <flux:button type="submit" variant="ghost" size="sm">Filtrar</flux:button>

    @if(request()->hasAny(['q','type','context']))
        <a href="{{ route('admin.media.index') }}"
           class="h-9 px-3 rounded-lg text-sm text-zinc-500 hover:text-zinc-800 dark:hover:text-zinc-200
                  inline-flex items-center gap-1 transition">
            <i class="fas fa-xmark text-xs"></i> Limpiar
        </a>
    @endif
</form>

{{-- ── Media grid ───────────────────────────────────────────────────── --}}
@if($media->isEmpty())
    <div class="text-center py-24 text-zinc-400 dark:text-zinc-600">
        <i class="fas fa-photo-film text-5xl mb-4 block opacity-40"></i>
        <p class="text-sm font-medium">No hay archivos multimedia</p>
        <p class="text-xs mt-1">Sube archivos usando el botón de arriba</p>
    </div>
@else
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3" id="media-grid">
        @foreach($media as $item)
            @php
                $ftype  = $item->fileType();     // 'image' | 'video' | 'document'
                $icon   = $typeIcons[$ftype]   ?? 'fa-file';
                $color  = $typeColors[$ftype]  ?? 'text-zinc-400';
                $bg     = $typeBg[$ftype]      ?? 'bg-zinc-100 dark:bg-zinc-800';
                $ext    = strtoupper(pathinfo($item->original_name, PATHINFO_EXTENSION) ?: 'FILE');

                $parentLabel = null;
                if ($item->mediable) {
                    $parentLabel = match(true) {
                        $item->mediable instanceof \App\Models\Post => ($item->mediable->getTranslation('title', app()->getLocale(), false) ?: ($item->mediable->getRawOriginal('title') ?? 'Sin título')),
                        default => class_basename($item->mediable_type) . ' #' . $item->mediable_id,
                    };
                }
            @endphp
            <div class="group relative rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 overflow-hidden shadow-sm hover:shadow-md hover:border-violet-300 dark:hover:border-violet-600 transition-all"
                 data-id="{{ $item->id }}">

                {{-- ── Thumbnail / preview ── --}}
                <div class="relative aspect-square overflow-hidden bg-zinc-50 dark:bg-zinc-800">
                    @if($item->isImage())
                        <img src="{{ $item->public_url }}"
                             alt="{{ $item->alt ?? $item->original_name }}"
                             class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
                             loading="lazy"
                             onerror="this.parentElement.innerHTML='<div class=\'w-full h-full flex items-center justify-center\'><i class=\'fas fa-image-slash text-2xl text-zinc-300\'></i></div>'">
                    @elseif($item->isVideo())
                        <div class="w-full h-full flex flex-col items-center justify-center gap-2 {{ $bg }}">
                            <i class="fas fa-play-circle text-4xl {{ $color }}"></i>
                            <span class="text-[10px] font-bold font-mono {{ $color }} uppercase">{{ $ext }}</span>
                        </div>
                    @else
                        <div class="w-full h-full flex flex-col items-center justify-center gap-2 {{ $bg }}">
                            <i class="fas {{ $icon }} text-3xl {{ $color }}"></i>
                            <span class="text-[10px] font-bold font-mono {{ $color }} uppercase">{{ $ext }}</span>
                        </div>
                    @endif

                    {{-- Type badge --}}
                    <div class="absolute top-2 left-2">
                        <span class="inline-flex items-center gap-1 rounded-full {{ $bg }} {{ $color }} px-1.5 py-0.5 text-[9px] font-semibold uppercase tracking-wide shadow-sm">
                            <i class="fas {{ $icon }} text-[8px]"></i>{{ $ftype }}
                        </span>
                    </div>

                    {{-- Hover actions overlay --}}
                    <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2 p-3">
                        <a href="{{ $item->public_url }}" target="_blank" rel="noopener"
                           class="inline-flex items-center justify-center size-8 rounded-lg bg-white/90 text-zinc-800 hover:bg-white transition"
                           title="Ver / abrir">
                            <i class="fas fa-arrow-up-right-from-square text-xs"></i>
                        </a>
                        <button type="button"
                                onclick="copyUrl('{{ addslashes($item->public_url) }}')"
                                class="inline-flex items-center justify-center size-8 rounded-lg bg-white/90 text-zinc-800 hover:bg-white transition"
                                title="Copiar URL">
                            <i class="fas fa-copy text-xs"></i>
                        </button>
                        <form method="POST" action="{{ route('admin.media.destroy', $item) }}"
                              onsubmit="return confirmDelete(event, '{{ addslashes($item->original_name) }}')">
                            @csrf @method('DELETE')
                            <button type="submit"
                                    class="inline-flex items-center justify-center size-8 rounded-lg bg-rose-500/90 hover:bg-rose-600 text-white transition"
                                    title="Eliminar">
                                <i class="fas fa-trash text-xs"></i>
                            </button>
                        </form>
                    </div>
                </div>

                {{-- ── Card footer ── --}}
                <div class="px-2.5 py-2 border-t border-zinc-100 dark:border-zinc-800">
                    <p class="text-xs font-medium text-zinc-700 dark:text-zinc-300 truncate" title="{{ $item->original_name }}">
                        {{ $item->original_name }}
                    </p>
                    <div class="flex items-center justify-between mt-0.5">
                        <span class="text-[10px] text-zinc-400">{{ $item->sizeLabel() }}</span>
                        @if($item->width && $item->height)
                            <span class="text-[10px] text-zinc-400">{{ $item->width }}×{{ $item->height }}</span>
                        @endif
                    </div>
                    @if($parentLabel)
                        <p class="text-[10px] text-violet-500 dark:text-violet-400 mt-0.5 truncate" title="{{ $parentLabel }}">
                            <i class="fas fa-link text-[8px]"></i> {{ $parentLabel }}
                        </p>
                    @endif
                    <p class="text-[10px] text-zinc-400 mt-0.5">{{ $item->created_at->format('d/m/Y') }}</p>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Pagination --}}
    @if($media->hasPages())
        <div class="mt-6">{{ $media->links() }}</div>
    @endif
@endif

{{-- ── Toast copy feedback ─────────────────────────────────────────── --}}
<div id="copy-toast"
     class="fixed bottom-6 right-6 z-50 hidden items-center gap-2 rounded-xl bg-zinc-900 text-white text-sm px-4 py-2.5 shadow-lg">
    <i class="fas fa-check text-emerald-400 text-xs"></i>
    URL copiada al portapapeles
</div>

<script>
function confirmDelete(e, name) {
    if (!confirm('¿Eliminar "' + name + '"?\nEsta acción no se puede deshacer.')) {
        e.preventDefault(); return false;
    }
    return true;
}

function copyUrl(url) {
    navigator.clipboard.writeText(url).then(() => {
        const t = document.getElementById('copy-toast');
        t.classList.remove('hidden');
        t.classList.add('flex');
        setTimeout(() => { t.classList.add('hidden'); t.classList.remove('flex'); }, 2200);
    });
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
            const res  = await fetch('{{ route("admin.media.upload") }}', { method: 'POST', body: fd });
            const data = await res.json();
            if (!data.success) console.warn('Error uploading', file.name, data.message);
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
