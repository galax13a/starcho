<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserBan extends Model
{
    protected $fillable = [
        'user_id',
        'banned_by',
        'reason',
        'notes',
        'banned_at',
        'expires_at',
        'lifted_at',
        'lifted_by',
    ];

    protected $casts = [
        'banned_at'  => 'datetime',
        'expires_at' => 'datetime',
        'lifted_at'  => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function bannedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'banned_by');
    }

    public function liftedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'lifted_by');
    }

    public function isActive(): bool
    {
        if ($this->lifted_at) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    public function isPermanent(): bool
    {
        return is_null($this->expires_at);
    }

    public function durationLabel(): string
    {
        if ($this->isPermanent()) {
            return __('admin_ui.users_ban.duration.permanent');
        }

        return $this->expires_at->diffForHumans($this->banned_at, true);
    }
}
