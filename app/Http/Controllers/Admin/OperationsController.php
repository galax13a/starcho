<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Models\OperationRun;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
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
        $pendingJobs = null;
        $queueQueryError = null;

        try {
            // Queue::size uses the configured backend, so this also works with Redis/SQS.
            $pendingJobs = Queue::connection($queueConnection)->size($queueName);
        } catch (Throwable $exception) {
            $queueQueryError = $exception->getMessage();
        }

        $failedDatabase = (string) config('queue.failed.database', $databaseConnection);
        $failedTable = (string) config('queue.failed.table', 'failed_jobs');
        $failedJobs = Schema::connection($failedDatabase)->hasTable($failedTable)
            ? DB::connection($failedDatabase)->table($failedTable)->count()
            : null;
        $oldestPendingJobAt = null;
        $queueTable = (string) config('queue.connections.'.$queueConnection.'.table', 'jobs');
        if (config('queue.connections.'.$queueConnection.'.driver') === 'database' && Schema::hasTable($queueTable)) {
            $oldestTimestamp = DB::table($queueTable)->min('created_at');
            $oldestPendingJobAt = $oldestTimestamp
                ? CarbonImmutable::createFromTimestampUTC((int) $oldestTimestamp)
                : null;
        }

        $variantsQueued = Media::query()->where('variants_status', 'queued')->count();
        $variantsProcessing = Media::query()->where('variants_status', 'processing')->count();
        $variantsFailed = Media::query()->where('variants_status', 'failed')->count();
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
        $queueWorkerMayBeStopped = $pendingJobs > 0
            && (! $queueRuns->first() || $queueRuns->first()->started_at?->lessThan(now()->subMinutes(10)));
        $sharedCacheRecommended = in_array($cacheStore, ['array', 'file'], true);

        return view('admin.operations.index', compact(
            'databaseConnection',
            'cacheStore',
            'queueConnection',
            'queueIsSynchronous',
            'queueName',
            'pendingJobs',
            'failedJobs',
            'oldestPendingJobAt',
            'queueQueryError',
            'variantsQueued',
            'variantsProcessing',
            'variantsFailed',
            'monitorTableReady',
            'schedulerRuns',
            'queueRuns',
            'failedOperations',
            'latestSchedulerRun',
            'schedulerIsStale',
            'queueWorkerMayBeStopped',
            'sharedCacheRecommended'
        ));
    }
}
