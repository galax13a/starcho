<?php

namespace App\Http\Controllers;

use App\Models\ContentSetting;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\PostTag;
use App\Models\SiteLanguage;
use App\Services\ContentRenderCache;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function index(string $locale, Request $request): View
    {
        $settings     = ContentSetting::cached();
        $perPage      = $settings?->posts_per_page ?? 12;
        $categorySlug = $request->query('category');
        $tagSlug      = $request->query('tag');
        $search       = trim($request->query('q', ''));

        $query = Post::query()
            ->where('type', Post::TYPE_POST)
            ->where('status', Post::STATUS_PUBLISHED)
            ->where(fn ($q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()))
            ->with(['categories', 'author'])
            ->latest('published_at');

        if ($search !== '') {
            $codes = SiteLanguage::activeCodes() ?: [$locale];
            $query->where(function ($q) use ($search, $codes) {
                foreach ($codes as $code) {
                    $q->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(title, '$.$code')) LIKE ?", ['%' . $search . '%'])
                      ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(excerpt, '$.$code')) LIKE ?", ['%' . $search . '%']);
                }
            });
        }

        $activeCategory = null;
        if ($categorySlug) {
            $activeCategory = PostCategory::where('slug', $categorySlug)->first();
            if ($activeCategory) {
                $query->whereHas('categories', fn ($q) => $q->where('post_category_post.post_category_id', $activeCategory->id));
            }
        }

        $activeTag = null;
        if ($tagSlug) {
            $activeTag = PostTag::where('slug', $tagSlug)->first();
            if ($activeTag) {
                $query->whereHas('tags', fn ($q) => $q->where('post_tag_post.post_tag_id', $activeTag->id));
            }
        }

        $posts    = $query->paginate($perPage)->withQueryString();
        $sidebar  = $this->sidebarData($locale);
        $langUrls = [];
        foreach (SiteLanguage::active() as $lang) {
            $langUrls[$lang->code] = url('/' . $lang->code . '/blog');
        }

        return view('blog.index', compact('posts', 'settings', 'locale', 'langUrls', 'sidebar', 'activeCategory', 'activeTag', 'search'));
    }

    private function sidebarData(string $locale): array
    {
        $categories = PostCategory::withCount(['posts' => fn ($q) => $q->where('status', Post::STATUS_PUBLISHED)])
            ->orderBy('sort_order')
            ->get();

        $recentPosts = Post::where('type', Post::TYPE_POST)
            ->where('status', Post::STATUS_PUBLISHED)
            ->where(fn ($q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()))
            ->with('categories')
            ->latest('published_at')
            ->limit(5)
            ->get();

        $tags = PostTag::withCount(['posts' => fn ($q) => $q->where('status', Post::STATUS_PUBLISHED)])
            ->having('posts_count', '>', 0)
            ->orderByDesc('posts_count')
            ->limit(20)
            ->get();

        return compact('categories', 'recentPosts', 'tags', 'locale');
    }

    public function show(string $locale, string $slug): mixed
    {
        $post = Post::where('type', Post::TYPE_POST)
            ->whereSlug($slug)
            ->where('status', Post::STATUS_PUBLISHED)
            ->where(fn ($q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()))
            ->with(['categories', 'tags', 'author'])
            ->first();

        if (!$post) {
            abort(404);
        }

        $post->increment('views_count');

        $settings        = ContentSetting::cached();
        $activeCodes     = SiteLanguage::activeCodes();
        $fallbackLocale  = null;
        $usingFallback   = false;

        $hasContentInLocale = filled($post->getTranslation('content', $locale, false));

        if (!$hasContentInLocale) {
            foreach ($activeCodes as $code) {
                if ($code !== $locale && filled($post->getTranslation('content', $code, false))) {
                    $fallbackLocale = $code;
                    $usingFallback  = true;
                    app()->setLocale($code);
                    break;
                }
            }
        }

        $readingTime = null;
        if ($settings?->reading_time_enabled) {
            $wpm         = $settings->reading_words_per_minute ?? 200;
            $contentText = strip_tags($post->getTranslation('content', app()->getLocale(), false) ?? '');
            $wordCount   = str_word_count($contentText);
            $readingTime = max(1, (int) ceil($wordCount / $wpm));
        }

        // Per-locale canonical URLs for the header language switcher
        $langUrls = [];
        foreach (SiteLanguage::active() as $lang) {
            $langUrls[$lang->code] = $post->publicUrl($lang->code);
        }

        $sidebar = $this->sidebarData($locale);

        $viewData = compact(
            'post', 'settings', 'locale', 'fallbackLocale', 'usingFallback', 'readingTime', 'langUrls', 'sidebar'
        );

        $html = app(ContentRenderCache::class)->remember(
            $post,
            $locale,
            fn () => view('blog.show', $viewData)->render()
        );

        if ($html !== null) {
            return response($html)->header('X-Starcho-Render-Cache', 'enabled');
        }

        return view('blog.show', $viewData);
    }
}
