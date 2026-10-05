<x-layouts::admin title="Operación del sitio">
    <div class="mx-auto max-w-7xl space-y-5">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <flux:heading size="xl" level="1">Operación del sitio</flux:heading>
                <flux:text class="text-sm text-zinc-500">Ejecuciones recientes del scheduler y la cola, más carga de variantes multimedia.</flux:text>
            </div>
            <a href="{{ route('admin.dashboard') }}" class="inline-flex h-9 items-center gap-2 rounded-lg border border-zinc-200 px-3 text-sm font-medium text-zinc-600 hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-300 dark:hover:bg-zinc-800">
                <i class="fas fa-arrow-left text-xs"></i> Dashboard
            </a>
        </div>

        @if(! $monitorTableReady)
            <div class="rounded-xl border border-amber-300 bg-amber-50 p-4 text-sm text-amber-800 dark:border-amber-800 dark:bg-amber-900/20 dark:text-amber-200">
                Falta ejecutar la migración de monitoreo. Aplica las migraciones para empezar a registrar ejecuciones.
            </div>
        @elseif($schedulerIsStale)
            <div class="rounded-xl border border-rose-300 bg-rose-50 p-4 text-sm text-rose-800 dark:border-rose-800 dark:bg-rose-900/20 dark:text-rose-200">
                No se observa una ejecución reciente del scheduler. Verifica que <code>php artisan schedule:run</code> corra cada minuto en una sola instancia compartida.
            </div>
        @endif

        @if($sharedCacheRecommended)
            <div class="rounded-xl border border-amber-300 bg-amber-50 p-4 text-sm text-amber-800 dark:border-amber-800 dark:bg-amber-900/20 dark:text-amber-200">
                El cache actual usa <strong>{{ $cacheStore }}</strong>. En varios servidores configura un cache compartido (por ejemplo database o Redis) para que los bloqueos <code>onOneServer</code> sean globales.
            </div>
        @endif

        @if($queueWorkerMayBeStopped)
            <div class="rounded-xl border border-amber-300 bg-amber-50 p-4 text-sm text-amber-800 dark:border-amber-800 dark:bg-amber-900/20 dark:text-amber-200">
                Hay trabajos pendientes y no se registran ejecuciones recientes de workers. Revisa el servicio <code>queue:work</code> en los servidores.
            </div>
        @endif

        @if($queueIsSynchronous)
            <div class="rounded-xl border border-amber-300 bg-amber-50 p-4 text-sm text-amber-800 dark:border-amber-800 dark:bg-amber-900/20 dark:text-amber-200">
                La conexión de cola es <code>sync</code>, que ejecuta variantes dentro de la subida. Para procesarlas en segundo plano, configura <code>QUEUE_CONNECTION=database</code> o Redis y mantén un worker activo.
            </div>
        @endif

        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-xs font-medium uppercase tracking-wide text-zinc-400">Scheduler</p>
                <p class="mt-2 text-lg font-semibold {{ $schedulerIsStale ? 'text-rose-600 dark:text-rose-300' : 'text-zinc-900 dark:text-zinc-100' }}">
                    {{ $latestSchedulerRun?->started_at?->diffForHumans() ?? 'Sin registro' }}
                </p>
                <p class="mt-1 text-xs text-zinc-500">Última tarea: {{ $latestSchedulerRun?->name ?? '—' }}</p>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-xs font-medium uppercase tracking-wide text-zinc-400">Cola {{ $queueName }}</p>
                <p class="mt-2 text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ $pendingJobs ?? 'No disponible' }} pendientes</p>
                <p class="mt-1 text-xs text-zinc-500">Conexión: {{ $queueConnection }} · fallidos: {{ $failedJobs ?? '—' }}</p>
                @if($oldestPendingJobAt)
                    <p class="mt-1 text-xs text-zinc-500">Más antiguo: {{ $oldestPendingJobAt->diffForHumans() }}</p>
                @endif
                @if($queueQueryError)
                    <p class="mt-1 truncate text-xs text-rose-600" title="{{ $queueQueryError }}">No fue posible consultar el backend.</p>
                @endif
            </div>
            <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-xs font-medium uppercase tracking-wide text-zinc-400">Variantes de imagen</p>
                <p class="mt-2 text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ $variantsQueued + $variantsProcessing }} en curso</p>
                <p class="mt-1 text-xs text-zinc-500">{{ $variantsQueued }} en cola · {{ $variantsProcessing }} procesando · {{ $variantsFailed }} fallidas</p>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-xs font-medium uppercase tracking-wide text-zinc-400">Conexiones de aplicación</p>
                <p class="mt-2 text-sm font-semibold text-zinc-900 dark:text-zinc-100">DB: {{ $databaseConnection }}</p>
                <p class="mt-1 text-xs text-zinc-500">Cache: {{ $cacheStore }} · Queue: {{ $queueConnection }}</p>
            </div>
        </div>

        <div class="rounded-xl border border-blue-200 bg-blue-50 p-4 text-sm text-blue-800 dark:border-blue-900 dark:bg-blue-950/40 dark:text-blue-200">
            En despliegues multinodo, todas las instancias deben compartir la misma base de datos, cache, backend de cola y discos de archivos. El scheduler usa bloqueos compartidos para ejecutar cada tarea en una sola instancia.
        </div>

        <section class="rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <h2 class="mb-3 text-sm font-semibold text-zinc-900 dark:text-zinc-100">Últimas ejecuciones del scheduler</h2>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[680px] text-left text-sm">
                    <thead class="border-b border-zinc-200 text-xs uppercase text-zinc-400 dark:border-zinc-700"><tr><th class="py-2 pr-4">Tarea</th><th class="py-2 pr-4">Inicio</th><th class="py-2 pr-4">Estado</th><th class="py-2">Duración</th></tr></thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @forelse($schedulerRuns as $run)
                            <tr><td class="py-2 pr-4 font-medium text-zinc-700 dark:text-zinc-200">{{ $run->name }}</td><td class="py-2 pr-4 text-zinc-500">{{ $run->started_at?->format('d/m/Y H:i:s') ?? '—' }}</td><td class="py-2 pr-4"><span class="rounded-full px-2 py-1 text-xs {{ $run->status === 'failed' ? 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-300' : ($run->status === 'running' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300' : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300') }}">{{ $run->status }}</span></td><td class="py-2 text-zinc-500">{{ $run->duration_ms !== null ? number_format($run->duration_ms).' ms' : '—' }}</td></tr>
                        @empty
                            <tr><td colspan="4" class="py-6 text-center text-zinc-500">Aún no hay ejecuciones registradas.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <h2 class="mb-3 text-sm font-semibold text-zinc-900 dark:text-zinc-100">Últimos trabajos de cola</h2>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[680px] text-left text-sm">
                    <thead class="border-b border-zinc-200 text-xs uppercase text-zinc-400 dark:border-zinc-700"><tr><th class="py-2 pr-4">Trabajo</th><th class="py-2 pr-4">Inicio</th><th class="py-2 pr-4">Estado</th><th class="py-2 pr-4">Intento</th><th class="py-2">Duración</th></tr></thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @forelse($queueRuns as $run)
                            <tr><td class="py-2 pr-4 font-medium text-zinc-700 dark:text-zinc-200">{{ $run->name }}</td><td class="py-2 pr-4 text-zinc-500">{{ $run->started_at?->format('d/m/Y H:i:s') ?? '—' }}</td><td class="py-2 pr-4"><span class="rounded-full px-2 py-1 text-xs {{ $run->status === 'failed' ? 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-300' : ($run->status === 'running' || $run->status === 'retrying' ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300' : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300') }}">{{ $run->status }}</span></td><td class="py-2 pr-4 text-zinc-500">{{ $run->attempt ?? '—' }}</td><td class="py-2 text-zinc-500">{{ $run->duration_ms !== null ? number_format($run->duration_ms).' ms' : '—' }}</td></tr>
                        @empty
                            <tr><td colspan="5" class="py-6 text-center text-zinc-500">Aún no hay trabajos procesados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <h2 class="mb-3 text-sm font-semibold text-zinc-900 dark:text-zinc-100">Fallos recientes</h2>
            <div class="space-y-3">
                @forelse($failedOperations as $run)
                    <article class="rounded-lg border border-rose-200 bg-rose-50 p-3 dark:border-rose-900/60 dark:bg-rose-950/20">
                        <div class="flex flex-wrap justify-between gap-2 text-sm font-semibold text-rose-800 dark:text-rose-200"><span>{{ $run->type }} · {{ $run->name }}</span><time class="text-xs font-normal">{{ $run->started_at?->format('d/m/Y H:i:s') ?? '—' }}</time></div>
                        <p class="mt-1 break-words text-xs text-rose-700 dark:text-rose-300">{{ $run->error ?: 'La tarea terminó con un código de error.' }}</p>
                    </article>
                @empty
                    <p class="py-5 text-center text-sm text-zinc-500">No hay fallos recientes registrados.</p>
                @endforelse
            </div>
        </section>
    </div>
</x-layouts::admin>
