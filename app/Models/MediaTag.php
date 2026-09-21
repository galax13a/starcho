<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Str;

class MediaTag extends Model
{
    protected $fillable = ['name', 'slug'];

    protected static function booted(): void
    {
        static::saving(function (MediaTag $tag): void {
            if (! filled($tag->slug)) {
                $tag->slug = Str::slug($tag->name);
            }
        });
    }

    public function media(): MorphToMany
    {
        return $this->morphedByMany(Media::class, 'taggable', 'media_taggables');
    }

    public function albums(): MorphToMany
    {
        return $this->morphedByMany(MediaAlbum::class, 'taggable', 'media_taggables');
    }
}
