<?php

namespace App\Models;

use App\Casts\EncryptedOrPlain;
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
        's3_use_path_style', 's3_url', 's3_folder',
        // DigitalOcean Spaces
        'do_key', 'do_secret', 'do_region', 'do_bucket', 'do_endpoint', 'do_cdn_url', 'do_folder',
        // Cloudflare R2
        'r2_account_id', 'r2_key', 'r2_secret', 'r2_bucket', 'r2_endpoint', 'r2_public_url', 'r2_folder',
        // Local
        'local_folder',
        'local_url',
        'image_variants_enabled',
        'image_variant_sizes',
        'image_preview_variant_size',
        'avatar_size',
    ];

    protected $casts = [
        's3_use_path_style' => 'boolean',
        'image_variants_enabled' => 'boolean',
        'image_variant_sizes' => 'array',
        // Cloud credentials encrypted at rest (graceful: tolerates legacy plaintext).
        's3_key'    => EncryptedOrPlain::class,
        's3_secret' => EncryptedOrPlain::class,
        'do_key'    => EncryptedOrPlain::class,
        'do_secret' => EncryptedOrPlain::class,
        'r2_key'    => EncryptedOrPlain::class,
        'r2_secret' => EncryptedOrPlain::class,
    ];

    /** Column names holding encrypted credentials (used by the migration). */
    public const ENCRYPTED_SECRETS = ['s3_key', 's3_secret', 'do_key', 'do_secret', 'r2_key', 'r2_secret'];

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

    /** Returns the configured upload folder prefix for the active driver (no trailing slash). */
    public function uploadFolder(): string
    {
        $raw = match ($this->default_driver) {
            's3'        => $this->s3_folder,
            'do_spaces' => $this->do_folder,
            'r2'        => $this->r2_folder,
            default     => $this->local_folder,
        };

        return trim($raw ?? 'uploads', '/');
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

    /**
     * Base URL for building public URLs when driver is local.
     * Uses the configured local_url (e.g. http://starcho.test) trimmed of trailing slash.
     * Falls back to config('app.url').
     */
    public function localBaseUrl(): string
    {
        $siteUrl = SiteSetting::cached()?->canonical_url;
        $url = filled($this->local_url)
            ? $this->local_url
            : (filled($siteUrl) ? $siteUrl : config('app.url', 'http://localhost'));

        return rtrim((string) $url, '/');
    }

    public function localPublicUrl(string $path): string
    {
        return $this->localBaseUrl() . '/storage/' . ltrim($path, '/');
    }

    public function imageVariantsEnabled(): bool
    {
        return (bool) $this->image_variants_enabled;
    }

    public function imageVariantSizes(): array
    {
        $sizes = collect($this->image_variant_sizes ?: [])
            ->map(fn ($size) => (int) $size)
            ->filter(fn (int $size) => $size >= 64 && $size <= 3840)
            ->unique()
            ->sort()
            ->values()
            ->all();

        return collect(array_merge([240], $sizes))
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    public function imagePreviewVariantSize(): int
    {
        $size = (int) ($this->image_preview_variant_size ?: 240);

        return in_array($size, $this->imageVariantSizes(), true)
            ? $size
            : 240;
    }

    public function avatarSize(): int
    {
        return min(512, max(64, (int) ($this->avatar_size ?: 190)));
    }
}
