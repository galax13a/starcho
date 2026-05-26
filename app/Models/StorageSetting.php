<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Singleton settings for storage driver configuration.
 * One row, always ID = 1. Use StorageSetting::singleton() everywhere.
 */
class StorageSetting extends Model
{
    protected $fillable = [
        'default_driver',
        // S3
        's3_key', 's3_secret', 's3_region', 's3_bucket', 's3_endpoint',
        's3_use_path_style', 's3_url',
        // DigitalOcean Spaces
        'do_key', 'do_secret', 'do_region', 'do_bucket', 'do_endpoint', 'do_cdn_url',
        // Cloudflare R2
        'r2_account_id', 'r2_key', 'r2_secret', 'r2_bucket', 'r2_endpoint', 'r2_public_url',
    ];

    protected $casts = [
        's3_use_path_style' => 'boolean',
    ];

    public static function singleton(): static
    {
        return static::firstOrCreate(['id' => 1], ['default_driver' => 'local']);
    }

    public function isLocal(): bool
    {
        return $this->default_driver === 'local';
    }

    public function isCloud(): bool
    {
        return ! $this->isLocal();
    }

    /** Returns the Laravel disk name to use based on the active driver. */
    public function diskName(): string
    {
        return match ($this->default_driver) {
            's3'        => 'starcho_s3',
            'do_spaces' => 'starcho_do',
            'r2'        => 'starcho_r2',
            default     => 'public',
        };
    }
}
