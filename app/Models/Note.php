<?php

namespace App\Models;

use App\Models\Concerns\EnforcesOwnership;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Note extends Model
{
    use EnforcesOwnership, SoftDeletes;

    protected $fillable = [
        'title',
        'content',
        'color',
        'important_date',
        'user_id',
    ];

    protected $casts = [
        'important_date' => 'date',
    ];

    public const COLORS = [
        '#6366f1',
        '#22c55e',
        '#f59e0b',
        '#ef4444',
        '#06b6d4',
        '#a855f7',
        '#e11d48',
        '#64748b',
    ];

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
