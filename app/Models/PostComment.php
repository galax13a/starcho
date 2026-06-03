<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A comment on a Post or Page. Supports up to MAX_DEPTH levels of nesting
 * (depth 0 = root comment, 1 = reply, 2 = reply-to-reply).
 */
class PostComment extends Model
{
    /** Depth 0 = root, 1 = reply, 2 = reply-to-reply (maximum). */
    public const MAX_DEPTH = 2;

    protected $fillable = [
        'post_id',
        'parent_id',
        'user_id',
        'depth',
        'author_name',
        'author_email',
        'body',
        'status',
    ];

    protected $casts = [
        'depth' => 'integer',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(PostComment::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(PostComment::class, 'parent_id')->oldest();
    }

    public function nestedReplies(): HasMany
    {
        return $this->replies()->with(['user', 'nestedReplies']);
    }
}
