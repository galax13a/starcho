<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiAssetGeneration extends Model
{
    public const TYPE_TEXT  = 'text';
    public const TYPE_IMAGE = 'image';
    public const TYPE_VIDEO = 'video';

    public const STATUS_PENDING    = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_COMPLETED  = 'completed';
    public const STATUS_FAILED     = 'failed';

    protected $fillable = [
        'user_id', 'type', 'provider', 'model', 'status', 'external_id',
        'media_id', 'prompt', 'params', 'error', 'cost_cents', 'price_cents', 'duration_ms',
    ];

    protected $casts = [
        'params'      => 'array',
        'cost_cents'  => 'integer',
        'price_cents' => 'integer',
        'duration_ms' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }

    public function isImage(): bool
    {
        return $this->type === self::TYPE_IMAGE;
    }

    public function isVideo(): bool
    {
        return $this->type === self::TYPE_VIDEO;
    }

    public function priceLabel(): string
    {
        return '$' . number_format($this->price_cents / 100, 2);
    }

    public function costLabel(): string
    {
        return '$' . number_format($this->cost_cents / 100, 2);
    }
}
