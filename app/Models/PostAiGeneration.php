<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostAiGeneration extends Model
{
    protected $fillable = [
        'post_id',
        'user_id',
        'provider',
        'model',
        'action',
        'locale',
        'prompt_text',
        'system_prompt',
        'request_payload',
        'response_text',
        'response_payload',
        'prompt_tokens',
        'completion_tokens',
        'cache_write_input_tokens',
        'cache_read_input_tokens',
        'reasoning_tokens',
        'total_tokens',
        'duration_ms',
        'rating',
        'rating_notes',
    ];

    protected $casts = [
        'request_payload' => 'array',
        'response_payload' => 'array',
        'prompt_tokens' => 'integer',
        'completion_tokens' => 'integer',
        'cache_write_input_tokens' => 'integer',
        'cache_read_input_tokens' => 'integer',
        'reasoning_tokens' => 'integer',
        'total_tokens' => 'integer',
        'duration_ms' => 'integer',
        'rating' => 'integer',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
