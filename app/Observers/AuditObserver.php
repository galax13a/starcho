<?php

namespace App\Observers;

use App\Services\AuditLogger;
use Illuminate\Database\Eloquent\Model;

/** Sends selected model lifecycle changes to the privacy-aware audit logger. */
class AuditObserver
{
    public function created(Model $model): void
    {
        app(AuditLogger::class)->recordModelChange($model, 'created');
    }

    public function updated(Model $model): void
    {
        app(AuditLogger::class)->recordModelChange($model, 'updated');
    }

    public function deleted(Model $model): void
    {
        app(AuditLogger::class)->recordModelChange($model, 'deleted');
    }
}
