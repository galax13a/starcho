<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

/**
 * An AI usage plan that sets per-user monthly quotas for text tokens, images,
 * and videos. null quota = unlimited; 0 = type not included in the plan.
 */
class AiPlan extends Model
{
    use HasTranslations;

    public array $translatable = ['name', 'description'];

    protected $fillable = [
        'name', 'description', 'slug', 'monthly_price', 'is_free', 'is_active', 'sort_order',
        'text_token_quota', 'image_quota', 'video_quota', 'monthly_budget_cents',
    ];

    protected $casts = [
        'monthly_price'        => 'decimal:2',
        'is_free'              => 'boolean',
        'is_active'            => 'boolean',
        'text_token_quota'     => 'integer',
        'image_quota'          => 'integer',
        'video_quota'          => 'integer',
        'monthly_budget_cents' => 'integer',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public static function free(): ?self
    {
        return static::where('is_free', true)->where('is_active', true)->orderBy('sort_order')->first();
    }

    public static function active()
    {
        return static::where('is_active', true)->orderBy('sort_order')->get();
    }

    /** Human label for a quota value: null = ilimitado, 0 = no incluido. */
    public function quotaLabel(string $type): string
    {
        $value = match ($type) {
            'text'  => $this->text_token_quota,
            'image' => $this->image_quota,
            'video' => $this->video_quota,
            default => null,
        };

        if ($value === null) {
            return 'Ilimitado';
        }

        if ($value === 0) {
            return 'No incluido';
        }

        return $type === 'text'
            ? number_format($value) . ' tokens'
            : number_format($value) . ($type === 'image' ? ' imágenes' : ' videos');
    }
}
