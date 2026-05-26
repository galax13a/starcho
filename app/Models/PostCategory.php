<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class PostCategory extends Model
{
    use SoftDeletes, HasTranslations;

    public $translatable = ['name', 'description'];

    protected $fillable = ['name', 'slug', 'description', 'color', 'sort_order'];

    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'post_category_post');
    }
}
