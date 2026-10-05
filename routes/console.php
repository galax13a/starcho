<?php

use App\Services\SitemapService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Tick once per minute so the command can apply the latest admin-selected cadence at runtime.
Schedule::command('starcho:publish-scheduled')->everyMinute()->withoutOverlapping();

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

// Refresh expired static XML on a schedule; model events invalidate it immediately after content edits.
Schedule::call(fn () => app(SitemapService::class)->refreshPublicCopyIfExpired())
    ->name('starcho:sitemap-refresh')
    ->everyMinute()
    ->withoutOverlapping(10)
    ->onOneServer();
