<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class MediaComment extends Model
{
    public const MAX_DEPTH = 2;

    protected $fillable = [
        'user_id',
        'parent_id',
        'commentable_id',
        'commentable_type',
        'depth',
        'body',
        'status',
    ];

    protected $casts = [
        'depth' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(MediaComment::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(MediaComment::class, 'parent_id')->oldest();
    }

    public function nestedReplies(): HasMany
    {
        return $this->replies()->with(['user', 'nestedReplies']);
    }
}
