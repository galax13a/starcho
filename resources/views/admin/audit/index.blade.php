<x-layouts::admin title="Bitácora de auditoría">
    <div class="mx-auto max-w-7xl space-y-5">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <flux:heading size="xl" level="1">Bitácora de auditoría</flux:heading>
                <flux:text class="text-sm text-zinc-500">Cambios relevantes en usuarios, roles, publicaciones y configuración. Las contraseñas y credenciales no se registran.</flux:text>
            </div>
            <a href="{{ route('admin.dashboard') }}" class="inline-flex h-9 items-center gap-2 rounded-lg border border-zinc-200 px-3 text-sm font-medium text-zinc-600 hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-300 dark:hover:bg-zinc-800">
                <i class="fas fa-arrow-left text-xs"></i> Dashboard
            </a>
        </div>

        <form method="GET" action="{{ route('admin.audit.index') }}" class="grid gap-3 rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900 sm:grid-cols-2 lg:grid-cols-6">
            <label class="text-xs font-medium text-zinc-500">Actor
                <input name="actor" value="{{ $filters['actor'] ?? '' }}" class="mt-1 h-10 w-full rounded-lg border-zinc-300 text-sm dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" placeholder="Nombre de usuario">
            </label>
            <label class="text-xs font-medium text-zinc-500">Acción
                <select name="action" class="mt-1 h-10 w-full rounded-lg border-zinc-300 text-sm dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                    <option value="">Todas</option>
                    @foreach(['created' => 'Creación', 'updated' => 'Actualización', 'deleted' => 'Eliminación', 'relations_updated' => 'Roles / permisos'] as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['action'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <label class="text-xs font-medium text-zinc-500">Elemento
                <select name="subject_type" class="mt-1 h-10 w-full rounded-lg border-zinc-300 text-sm dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                    <option value="">Todos</option>
                    @foreach($subjectTypes as $type => $label)
                        <option value="{{ $type }}" @selected(($filters['subject_type'] ?? '') === $type)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <label class="text-xs font-medium text-zinc-500">Desde
                <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="mt-1 h-10 w-full rounded-lg border-zinc-300 text-sm dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
            </label>
            <label class="text-xs font-medium text-zinc-500">Hasta
                <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="mt-1 h-10 w-full rounded-lg border-zinc-300 text-sm dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
            </label>
            <div class="flex items-end gap-2">
                <button class="inline-flex h-10 flex-1 items-center justify-center gap-2 rounded-lg bg-zinc-900 px-3 text-sm font-semibold text-white hover:bg-zinc-700 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-white">
                    <i class="fas fa-filter text-xs"></i> Filtrar
                </button>
                <a href="{{ route('admin.audit.index') }}" class="inline-flex h-10 items-center justify-center rounded-lg border border-zinc-200 px-3 text-sm text-zinc-600 hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-300 dark:hover:bg-zinc-800" aria-label="Limpiar filtros">
                    <i class="fas fa-xmark"></i>
                </a>
            </div>
        </form>

        <section class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-zinc-100 px-4 py-3 dark:border-zinc-800">
                <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Cambios recientes</h2>
                <span class="text-xs text-zinc-500">{{ $logs->total() }} registros · 30 por página</span>
            </div>

            <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @forelse($logs as $log)
                    @php
                        $actionLabel = ['created' => 'creó', 'updated' => 'actualizó', 'deleted' => 'eliminó', 'relations_updated' => 'cambió roles o permisos'][$log->action] ?? $log->action;
                        $subjectLabel = $subjectTypes[$log->subject_type] ?? class_basename($log->subject_type);
                    @endphp
                    <article class="px-4 py-4 sm:px-5">
                        <div class="flex flex-wrap items-start justify-between gap-2">
                            <div>
                                <p class="text-sm text-zinc-800 dark:text-zinc-100">
                                    <span class="font-semibold">{{ $log->actor?->name ?? $log->actor_name ?? 'Sistema' }}</span>
                                    {{ $actionLabel }}
                                    <span class="font-medium">{{ $subjectLabel }}</span>
                                    <span class="text-zinc-500">· {{ $log->subject_label }}</span>
                                </p>
                                <p class="mt-1 text-xs text-zinc-500">
                                    {{ $log->created_at?->format('d/m/Y H:i:s') ?? '—' }}
                                    @if($log->ip_address) · IP {{ $log->ip_address }} @endif
                                </p>
                            </div>
                            <span class="rounded-full bg-zinc-100 px-2.5 py-1 text-[11px] font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">#{{ $log->id }}</span>
                        </div>

                        @if($log->changes)
                            <details class="mt-3 rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-800/60">
                                <summary class="cursor-pointer text-xs font-semibold text-zinc-600 dark:text-zinc-300">Ver cambios</summary>
                                <pre class="mt-2 max-h-72 overflow-auto whitespace-pre-wrap break-words text-[11px] leading-relaxed text-zinc-600 dark:text-zinc-300">{{ json_encode($log->changes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                            </details>
                        @endif
                    </article>
                @empty
                    <p class="px-4 py-12 text-center text-sm text-zinc-500">No hay cambios que coincidan con estos filtros.</p>
                @endforelse
            </div>

            @if($logs->hasPages())
                <div class="border-t border-zinc-100 px-4 py-3 dark:border-zinc-800">{{ $logs->links() }}</div>
            @endif
        </section>
    </div>
</x-layouts::admin>
