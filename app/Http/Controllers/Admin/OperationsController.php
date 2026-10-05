<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContentSetting;
use App\Models\Media;
use App\Models\OperationRun;
use App\Models\Post;
use App\Models\StorageSetting;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class OperationsController extends Controller
{
    public function index(): View
    {
        $databaseConnection = (string) config('database.default');
        $cacheStore = (string) config('cache.default');
        $queueConnection = (string) config('queue.default');
        $queueName = (string) config('queue.connections.'.$queueConnection.'.queue', 'default');
        $queueIsSynchronous = $queueConnection === 'sync';
        $databaseHealth = $this->probeDatabase($databaseConnection);
        $cacheHealth = $this->probeCache($cacheStore);
        $storageSetting = StorageSetting::singleton();
        $storageDisk = $storageSetting->diskName();
        $storageHealth = $this->probeStorage($storageDisk);
        $pendingJobs = null;
        $queueQueryFailed = false;

        try {
            // Queue::size uses the configured backend, so this also works with Redis/SQS.
            $pendingJobs = Queue::connection($queueConnection)->size($queueName);
        } catch (Throwable) {
            $queueQueryFailed = true;
        }

        $failedDatabase = (string) config('queue.failed.database', $databaseConnection);
        $failedTable = (string) config('queue.failed.table', 'failed_jobs');
        $failedJobs = null;
        try {
            $failedJobs = Schema::connection($failedDatabase)->hasTable($failedTable)
                ? DB::connection($failedDatabase)->table($failedTable)->count()
                : null;
        } catch (Throwable) {
            // A broken database must show as unavailable instead of breaking the health page.
        }

        $oldestPendingJobAt = null;
        $queueTable = (string) config('queue.connections.'.$queueConnection.'.table', 'jobs');
        try {
            if (config('queue.connections.'.$queueConnection.'.driver') === 'database' && Schema::hasTable($queueTable)) {
                $oldestTimestamp = DB::table($queueTable)->min('created_at');
                $oldestPendingJobAt = $oldestTimestamp
                    ? CarbonImmutable::createFromTimestampUTC((int) $oldestTimestamp)
                    : null;
            }
        } catch (Throwable) {
            // The pending count above already carries the queue backend health signal.
        }

        $variantStateReady = Schema::hasColumn('media', 'variants_status');
        $variantsQueued = $variantStateReady ? Media::query()->where('variants_status', 'queued')->count() : 0;
        $variantsProcessing = $variantStateReady ? Media::query()->where('variants_status', 'processing')->count() : 0;
        $variantsFailed = $variantStateReady ? Media::query()->where('variants_status', 'failed')->count() : 0;
        $monitorTableReady = Schema::hasTable('operation_runs');
        $schedulerRuns = $monitorTableReady
            ? OperationRun::query()->where('type', 'scheduler')->latest('started_at')->limit(12)->get()
            : collect();
        $queueRuns = $monitorTableReady
            ? OperationRun::query()->where('type', 'queue')->latest('started_at')->limit(12)->get()
            : collect();
        $failedOperations = $monitorTableReady
            ? OperationRun::query()->where('status', 'failed')->latest('started_at')->limit(12)->get()
            : collect();
        $latestSchedulerRun = $schedulerRuns->first();
        $schedulerIsStale = ! $latestSchedulerRun || $latestSchedulerRun->started_at?->lessThan(now()->subMinutes(5));
        $latestPublishRun = $monitorTableReady
            ? OperationRun::query()
                ->where('type', 'scheduler')
                ->where('name', 'like', '%starcho:publish-scheduled%')
                ->latest('started_at')
                ->first()
            : null;
        $publishSchedulerIsStale = ! $latestPublishRun || $latestPublishRun->started_at?->lessThan(now()->subMinutes(5));
        $scheduledPosts = Post::query()->where('status', Post::STATUS_SCHEDULED);
        $scheduledPostsCount = (clone $scheduledPosts)->count();
        $scheduledPostsDueCount = (clone $scheduledPosts)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->count();
        $nextScheduledPostAt = (clone $scheduledPosts)
            ->whereNotNull('published_at')
            ->where('published_at', '>', now())
            ->min('published_at');
        $nextScheduledPostAt = $nextScheduledPostAt ? CarbonImmutable::parse($nextScheduledPostAt) : null;
        $publishIntervalMinutes = ContentSetting::singleton()->scheduledPublishIntervalMinutes();
        $queueWorkerMayBeStopped = $pendingJobs > 0
            && (! $queueRuns->first() || $queueRuns->first()->started_at?->lessThan(now()->subMinutes(10)));
        $sharedCacheRecommended = in_array($cacheStore, ['array', 'file'], true);

        return view('admin.operations.index', compact(
            'databaseConnection',
            'cacheStore',
            'queueConnection',
            'queueIsSynchronous',
            'queueName',
            'databaseHealth',
            'cacheHealth',
            'storageHealth',
            'storageDisk',
            'pendingJobs',
            'failedJobs',
            'oldestPendingJobAt',
            'queueQueryFailed',
            'variantsQueued',
            'variantsProcessing',
            'variantsFailed',
            'variantStateReady',
            'monitorTableReady',
            'schedulerRuns',
            'queueRuns',
            'failedOperations',
            'latestSchedulerRun',
            'schedulerIsStale',
            'latestPublishRun',
            'publishSchedulerIsStale',
            'scheduledPostsCount',
            'scheduledPostsDueCount',
            'nextScheduledPostAt',
            'publishIntervalMinutes',
            'queueWorkerMayBeStopped',
            'sharedCacheRecommended'
        ));
    }

    /** Verify database connectivity using a read-only, portable query. */
    private function probeDatabase(string $connection): array
    {
        try {
            DB::connection($connection)->select('select 1');

            return ['status' => 'ok', 'detail' => 'Conexión verificada'];
        } catch (Throwable) {
            return ['status' => 'error', 'detail' => 'No fue posible conectar'];
        }
    }

    /** Write and remove a short-lived cache key to verify read/write access. */
    private function probeCache(string $store): array
    {
        $key = 'starcho:health-check:'.Str::uuid();
        $cache = null;

        try {
            $cache = Cache::store($store);
            $cache->put($key, 'ok', 10);
            $healthy = $cache->get($key) === 'ok';

            return ['status' => $healthy ? 'ok' : 'error', 'detail' => $healthy ? 'Lectura y escritura verificadas' : 'La prueba no devolvió el valor esperado'];
        } catch (Throwable) {
            return ['status' => 'error', 'detail' => 'No fue posible leer o escribir'];
        } finally {
            if ($cache !== null) {
                try {
                    $cache->forget($key);
                } catch (Throwable) {
                    // Expiration is a fallback if a backend is unavailable during cleanup.
                }
            }
        }
    }

    /** Probe the active media disk and clean up the uniquely named test object. */
    private function probeStorage(string $diskName): array
    {
        $disk = null;
        $path = 'health-checks/.starcho-'.Str::uuid().'.tmp';
        $health = ['status' => 'error', 'detail' => 'No fue posible verificar escritura y lectura'];

        try {
            $disk = Storage::disk($diskName);
            $written = $disk->put($path, 'starcho storage health probe');
            $healthy = $written !== false && $disk->exists($path);
            $health = ['status' => $healthy ? 'ok' : 'error', 'detail' => $healthy ? 'Escritura y lectura verificadas' : 'La prueba no encontró el archivo'];
        } catch (Throwable) {
            // Do not expose bucket credentials, endpoints, or filesystem paths in the UI.
        } finally {
            if ($disk !== null) {
                try {
                    if ($disk->exists($path) && ! $disk->delete($path)) {
                        $health = ['status' => 'warning', 'detail' => 'Prueba correcta, pero no se pudo limpiar el archivo temporal'];
                    }
                } catch (Throwable) {
                    $health = ['status' => 'warning', 'detail' => 'La limpieza del archivo de prueba requiere revisión'];
                }
            }
        }

        return $health;
    }
}
