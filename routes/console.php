<?php

use App\Models\OperationRun;
use App\Services\SitemapService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// The shared cache lock lets exactly one app instance publish when several web nodes run cron.
// Keep the minute tick so the admin-selected publication cadence can change without redeploying.
Schedule::command('starcho:publish-scheduled')
    ->everyMinute()
    ->withoutOverlapping()
    ->onOneServer();

// Backfill legacy protected uploads automatically in bounded batches after deployments/migrations.
Schedule::command('starcho:secure-media --limit=100')
    ->everyMinute()
    ->withoutOverlapping(10)
    ->onOneServer()
    ->appendOutputTo(storage_path('logs/secure-media.log'));

// A weekly, read-only scan catches orphaned/missing objects and quota counter drift
// without adding storage-listing latency or cost to normal requests.
Schedule::command('starcho:storage-audit')
    ->weeklyOn(1, '03:30')
    ->withoutOverlapping(90)
    ->onOneServer()
    ->appendOutputTo(storage_path('logs/storage-audit.log'));

// Keep the operational timeline bounded while retaining enough history for troubleshooting.
Schedule::call(fn () => OperationRun::query()->where('started_at', '<', now()->subDays(30))->delete())
    ->name('starcho:prune-operation-runs')
    ->dailyAt('03:45')
    ->withoutOverlapping(60)
    ->onOneServer();

// Refresh expired static XML on a schedule; model events invalidate it immediately after content edits.
Schedule::call(fn () => app(SitemapService::class)->refreshPublicCopyIfExpired())
    ->name('starcho:sitemap-refresh')
    ->everyMinute()
    ->withoutOverlapping(10)
    ->onOneServer();
