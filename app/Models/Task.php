<?php

namespace App\Models;

use App\Models\Concerns\EnforcesOwnership;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Task extends Model
{
    use EnforcesOwnership, HasTranslations, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'status',
        'priority',
        'due_date',
        'assigned_to',
        'user_id',
    ];

    protected $casts = [
        'due_date' => 'date',
    ];

    public $translatable = ['title', 'description'];

    const STATUS = [
        'pending' => 'Pendiente',
        'in_progress' => 'En progreso',
        'completed' => 'Completada',
        'cancelled' => 'Cancelada',
    ];

    const PRIORITY = [
        'low' => 'Baja',
        'medium' => 'Media',
        'high' => 'Alta',
        'urgent' => 'Urgente',
    ];

    const STATUS_COLORS = [
        'pending' => 'zinc',
        'in_progress' => 'blue',
        'completed' => 'green',
        'cancelled' => 'red',
    ];

    const PRIORITY_COLORS = [
        'low' => 'zinc',
        'medium' => 'yellow',
        'high' => 'orange',
        'urgent' => 'red',
    ];

    /** @return BelongsTo<User, $this> */
    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
