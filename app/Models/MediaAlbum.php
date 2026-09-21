<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MediaAlbum extends Model
{
    public const VISIBILITIES = ['public', 'authenticated', 'protected', 'private'];

    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'description',
        'password_enabled',
        'password',
        'visibility',
    ];

    protected $casts = [
        'password_enabled' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (MediaAlbum $album): void {
            if (! filled($album->slug)) {
                $album->slug = Str::slug($album->name);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsToMany<Media, $this> */
    public function media(): BelongsToMany
    {
        return $this->belongsToMany(Media::class, 'media_album_media')
            ->withPivot('sort_order')
            ->withTimestamps()
            ->orderByPivot('sort_order')
            ->latest('media.created_at');
    }

    public function tags(): MorphToMany
    {
        return $this->morphToMany(MediaTag::class, 'taggable', 'media_taggables')->withTimestamps();
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(MediaComment::class, 'commentable')->latest();
    }

    public function ratings(): MorphMany
    {
        return $this->morphMany(MediaRating::class, 'ratable');
    }

    public function setPlainPassword(?string $password): void
    {
        $this->password = filled($password) ? Hash::make($password) : null;
        $this->password_enabled = filled($password);

        if (filled($password)) {
            $this->visibility = Media::stricterVisibility($this->visibility ?: 'public', 'protected');
        }
    }

    /** A legacy password flag is always treated as protected. */
    public function effectiveVisibility(): string
    {
        $visibility = in_array($this->visibility, self::VISIBILITIES, true)
            ? $this->visibility
            : 'private';

        if ($this->password_enabled && in_array($visibility, ['public', 'authenticated'], true)) {
            return 'protected';
        }

        return $visibility;
    }

    public function getAverageRatingAttribute(): ?float
    {
        $average = $this->ratings()->avg('rating');

        return $average === null ? null : round((float) $average, 1);
    }
}
