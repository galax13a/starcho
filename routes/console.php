<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Tick once per minute so the command can apply the latest admin-selected cadence at runtime.
Schedule::command('starcho:publish-scheduled')->everyMinute()->withoutOverlapping();
