<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** A short-lived operational log for scheduled commands and queue attempts. */
class OperationRun extends Model
{
    protected $fillable = [
        'type',
        'name',
        'run_id',
        'status',
        'attempt',
        'started_at',
        'finished_at',
        'duration_ms',
        'error',
    ];

    protected $casts = [
        'attempt' => 'integer',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'duration_ms' => 'integer',
    ];
}
