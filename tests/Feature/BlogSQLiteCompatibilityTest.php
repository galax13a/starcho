<?php

use App\Models\Post;
use App\Models\SiteLanguage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    SiteLanguage::create([
        'code' => 'en',
        'name' => 'English',
        'native_name' => 'English',
        'active' => true,
        'sort_order' => 1,
    ]);
    SiteLanguage::create([
        'code' => 'es',
        'name' => 'Spanish',
        'native_name' => 'Español',
        'active' => true,
        'sort_order' => 2,
    ]);
});

function blogPost(array $attributes = []): Post
{
    $author = User::factory()->create();

    return Post::create(array_merge([
        'type' => Post::TYPE_POST,
        'title' => ['en' => 'SQLite portable search', 'es' => 'Búsqueda portátil SQLite'],
        'slug' => ['en' => 'sqlite-portable-search', 'es' => 'busqueda-portatil-sqlite'],
        'excerpt' => ['en' => 'A portable database example', 'es' => 'Un ejemplo portable'],
        'content' => ['en' => 'Content', 'es' => 'Contenido'],
        'status' => Post::STATUS_PUBLISHED,
        'published_at' => now()->subMinute(),
        'author_id' => $author->id,
        'user_id' => $author->id,
    ], $attributes));
}

it('checks translated slugs with SQLite JSON selectors', function () {
    $post = blogPost();

    expect(Post::slugExists('sqlite-portable-search'))->toBeTrue()
        ->and(Post::slugExists('busqueda-portatil-sqlite'))->toBeTrue()
        ->and(Post::slugExists('sqlite-portable-search', $post->id))->toBeFalse()
        ->and(Post::whereSlug('busqueda-portatil-sqlite')->first()?->is($post))->toBeTrue();
});

it('checks slug translations from inactive locales with SQLite', function () {
    SiteLanguage::create([
        'code' => 'fr',
        'name' => 'French',
        'native_name' => 'Français',
        'active' => false,
        'sort_order' => 3,
    ]);

    $post = blogPost([
        'slug' => [
            'en' => 'portable-slug',
            'es' => 'slug-portatil',
            'fr' => 'slug-historique',
        ],
    ]);

    expect(Post::slugExists('slug-historique'))->toBeTrue()
        ->and(Post::whereSlug('slug-historique')->first()?->is($post))->toBeTrue();
});

it('searches translated titles and excerpts from the public blog on SQLite', function () {
    $post = blogPost();

    $this->get('/en/blog?q=portable')
        ->assertOk()
        ->assertSee('SQLite portable search');

    $this->get('/en/blog?q=ejemplo')
        ->assertOk()
        ->assertSee('SQLite portable search');
});
