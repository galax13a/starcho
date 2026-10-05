<?php

namespace App\Services;

use App\Models\OperationRun;
use Illuminate\Console\Events\ScheduledTaskFailed;
use Illuminate\Console\Events\ScheduledTaskFinished;
use Illuminate\Console\Events\ScheduledTaskStarting;
use Illuminate\Queue\Events\JobExceptionOccurred;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

/** Persists bounded operational breadcrumbs for scheduled and queued work. */
class OperationMonitor
{
    /** @var array<int, int> Scheduler task object id => operation_runs primary key. */
    private array $scheduledRunIds = [];

    public function schedulerStarting(ScheduledTaskStarting $event): void
    {
        if (! $this->available() || ! $this->isStarchoTask($event->task)) {
            return;
        }

        $run = OperationRun::query()->create([
            'type' => 'scheduler',
            'name' => Str::limit($event->task->getSummaryForDisplay(), 255, ''),
            'run_id' => (string) Str::uuid(),
            'status' => 'running',
            'started_at' => now(),
        ]);

        $this->scheduledRunIds[spl_object_id($event->task)] = (int) $run->getKey();
    }

    public function schedulerFinished(ScheduledTaskFinished $event): void
    {
        $runId = $this->scheduledRunIds[spl_object_id($event->task)] ?? null;
        if (! $runId || ! $this->available()) {
            return;
        }

        OperationRun::query()->whereKey($runId)->update([
            'status' => $event->task->exitCode === 0 ? 'succeeded' : 'failed',
            'finished_at' => now(),
            'duration_ms' => max(0, (int) round($event->runtime * 1000)),
            'updated_at' => now(),
        ]);
    }

    public function schedulerFailed(ScheduledTaskFailed $event): void
    {
        if (! $this->available() || ! $this->isStarchoTask($event->task)) {
            return;
        }

        $taskId = spl_object_id($event->task);
        $runId = $this->scheduledRunIds[$taskId] ?? null;
        $values = [
            'status' => 'failed',
            'finished_at' => now(),
            'error' => Str::limit($event->exception->getMessage(), 4000),
            'updated_at' => now(),
        ];

        if ($runId) {
            OperationRun::query()->whereKey($runId)->update($values);
            unset($this->scheduledRunIds[$taskId]);

            return;
        }

        // Defensive fallback for failures dispatched without a matching start event.
        OperationRun::query()->create($values + [
            'type' => 'scheduler',
            'name' => Str::limit($event->task->getSummaryForDisplay(), 255, ''),
            'run_id' => (string) Str::uuid(),
            'started_at' => now(),
        ]);
    }

    public function queueStarting(JobProcessing $event): void
    {
        if (! $this->available()) {
            return;
        }

        OperationRun::query()->create([
            'type' => 'queue',
            'name' => $this->queueJobName($event->job),
            'run_id' => $this->queueRunId($event->connectionName, $event->job),
            'status' => 'running',
            'attempt' => $event->job->attempts(),
            'started_at' => now(),
        ]);
    }

    public function queueProcessed(JobProcessed $event): void
    {
        $this->finishQueueRun($event->connectionName, $event->job, 'succeeded');
    }

    public function queueException(JobExceptionOccurred $event): void
    {
        $this->finishQueueRun($event->connectionName, $event->job, 'retrying', $event->exception);
    }

    public function queueFailed(JobFailed $event): void
    {
        $this->finishQueueRun($event->connectionName, $event->job, 'failed', $event->exception);
    }

    private function finishQueueRun(string $connection, object $job, string $status, ?Throwable $exception = null): void
    {
        if (! $this->available()) {
            return;
        }

        $runId = $this->queueRunId($connection, $job);
        $run = OperationRun::query()->where('type', 'queue')->where('run_id', $runId)->latest('id')->first();
        $values = [
            'status' => $status,
            'finished_at' => now(),
            'duration_ms' => $run?->started_at ? max(0, (int) $run->started_at->diffInMilliseconds(now())) : null,
            'error' => $exception ? Str::limit($exception->getMessage(), 4000) : null,
            'updated_at' => now(),
        ];

        if ($run) {
            $run->forceFill($values)->save();

            return;
        }

        // Keep the failure visible even if the worker restarted between queue events.
        OperationRun::query()->create($values + [
            'type' => 'queue',
            'name' => $this->queueJobName($job),
            'run_id' => $runId,
            'attempt' => $job->attempts(),
            'started_at' => now(),
        ]);
    }

    private function queueRunId(string $connection, object $job): string
    {
        // Include the attempt so retries remain individually inspectable.
        return hash('sha256', implode('|', [
            $connection,
            (string) $job->getQueue(),
            (string) $job->getJobId(),
            (string) $job->attempts(),
        ]));
    }

    private function queueJobName(object $job): string
    {
        return Str::limit($job->getName().' · '.$job->getQueue(), 255, '');
    }

    private function isStarchoTask(object $task): bool
    {
        return str_contains($task->getSummaryForDisplay(), 'starcho:')
            || str_contains((string) ($task->command ?? ''), 'starcho:');
    }

    private function available(): bool
    {
        // Workers and schedule:run can start during deployment before migrations finish.
        return Schema::hasTable('operation_runs');
    }
}
