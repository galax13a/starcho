<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StoragePlan extends Model
{
    protected $fillable = [
        'name', 'slug', 'storage_limit_bytes',
        'monthly_price', 'is_free', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'is_free'    => 'boolean',
        'is_active'  => 'boolean',
        'monthly_price' => 'decimal:2',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'storage_plan_id');
    }

    public static function free(): ?static
    {
        return static::where('is_free', true)->orderBy('sort_order')->first();
    }

    public static function active(): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('is_active', true)->orderBy('sort_order')->get();
    }

    /** Human-readable storage limit (e.g. "5 MB", "1 GB"). */
    public function limitLabel(): string
    {
        $bytes = $this->storage_limit_bytes;

        if ($bytes >= 1_073_741_824) {
            return round($bytes / 1_073_741_824, 0) . ' GB';
        }

        if ($bytes >= 1_048_576) {
            return round($bytes / 1_048_576, 0) . ' MB';
        }

        return round($bytes / 1024, 0) . ' KB';
    }
}
