<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Task extends Model
{
    use SoftDeletes, HasTranslations;

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
        'pending'     => 'Pendiente',
        'in_progress' => 'En progreso',
        'completed'   => 'Completada',
        'cancelled'   => 'Cancelada',
    ];

    const PRIORITY = [
        'low'    => 'Baja',
        'medium' => 'Media',
        'high'   => 'Alta',
        'urgent' => 'Urgente',
    ];

    const STATUS_COLORS = [
        'pending'     => 'zinc',
        'in_progress' => 'blue',
        'completed'   => 'green',
        'cancelled'   => 'red',
    ];

    const PRIORITY_COLORS = [
        'low'    => 'zinc',
        'medium' => 'yellow',
        'high'   => 'orange',
        'urgent' => 'red',
    ];

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
